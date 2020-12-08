<?php

/**
 * Payment Helper
 *
 * @package     Makent
 * @subpackage  Helper
 * @category    Helper
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Http\Helper;

use App\Models\RoomsPrice;
use App\Models\Calendar;
use App\Models\Currency;
use App\Models\Rooms;
use App\Models\SpecialOffer;
use App\Models\Reservation;
use App\Models\Fees;
use App\Models\HostPenalty;
use App\Models\CouponCode;
use App\Models\Payouts;
use App\Models\Referrals;
use App\Models\Messages;
use App\Models\AppliedTravelCredit;
use App\Models\MultipleRooms;
use DateTime;
use Session;
use Auth;
use FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;



class PaymentHelper
{


   public function Socket($reservation_details,$type){
    
    $last_msg = Messages::where('reservation_id',$reservation_details->id)->orderByDesc('id')->limit(1)->get();
    $instant_message = $this->InstantMessage($last_msg);
    $result['instant_message'] = $instant_message[0];
    $type = ($type=='host') ? $reservation_details->host_id : $reservation_details->user_id;
    $result['count'] = $this->InstantMessageCount($type);
    $result['type']  = 'booking';
    $result['inbox'] ='yes';
    $redis = \LRedis::connection();
    $redis->publish('chat', json_encode($result));

   }


   //Instant Message Count
   public function InstantMessageCount($host_id){
      //Guest Message Count Update 
      $guest_count =  Messages::where('user_to', Auth::user()->id)->where('read', '0')->where('archive','0')->groupby('reservation_id')->get()->count();
       
      //Host Message Count Update 
      $host_count  =  Messages::where('user_to', $host_id)->where('read', '0')->where('archive','0')->groupby('reservation_id')->get()->count();

      $guest = array('guest_id'=>Auth::user()->id,'guest_count'=>$guest_count);
      $host = array('host_id'=>$host_id,'host_count'=>$host_count);
      return array_merge($guest,$host);

   }

  //Instant Message - Web Socket
   public function InstantMessage($messages)
   {
      $instant_message = array();
      $instant_message = $messages->map(function($message_data){
            $message = $this->message_data($message_data);
            if(!is_null($message_data->reservation)){
            $message['reservation'] = $this->reservation_data($message_data->reservation,$message_data->user_to);
            $message['special_offer'] = $this->special_data($message_data->special_offer);

            }
            return $message;
      });
      
      return $instant_message;
   }


   public function message_data($message)
   {
        $messages = $message->only(['id','room_id','list_type','reservation_id','user_to','user_from','message','message_type','special_offer_id','created_time','inbox_thread_count','message_type_text','host_check']);
        $messages['user_name'] = $message->user_details->first_name;
        $messages['user_src'] = $message->user_details->profile_picture_src;
        $messages['other_user_name'] = $message->users->first_name;
        $messages['other_user_src'] = $message->users->profile_picture_src;
        return $messages;
    }

    public function reservation_data($reservation,$user_to)
    {
        $reservation_data = $reservation->only(['id','code','room_id','list_type','host_id','user_id','host_fee','dates','host_payout','number_of_guests','avablity','status','dates_subject','type','status_color','subtotal','host_fee','total','status_language']);

        $currency = $reservation->currency->only(['id','symbol','code']);  
        $reservation_data['currency'] = $currency;
        
        $users = $reservation->users->only('first_name');
        $profile_picture = $reservation->users->profile_picture->only('src');
        
        $reservation_data['inbox_url'] = $reservation->getInboxUrl($user_to);
        $reservation_data['user_name'] = $users['first_name'];
        $reservation_data['profile_picture'] = $profile_picture['src'];

        if($reservation->list_type == "Experiences") {
          $reservation->rooms = $reservation->host_experiences;
        }
        
        $rooms = $reservation->rooms->only('name');
        $reservation_data['room_name'] = $rooms['name'];
        $host_user = $reservation->rooms->users->only('first_name');
        $host_profile_picture = $reservation->rooms->users->profile_picture->only('src');

        $reservation_data['host_name'] = $host_user['first_name'];
        $reservation_data['host_profile_picture'] = $host_profile_picture['src'];

        $rooms_address = $reservation->rooms->rooms_address->only('address_line_1','address_line_2','city', 'state', 'country_name');
        if($reservation->special_offer){
            $special_offer = $reservation->special_offer->only(['id','reservation_id','dates','number_of_guests','is_booked','price','avablity','checkin','checkout','room_id','type','checkin_arrive','checkout_depart']);
            $special_offer_rooms = $reservation->special_offer->rooms->only(['link','name']);
            $reservation_data['special_offer'] = array_merge($special_offer,$special_offer_rooms);
        }

        $reservation_data['rooms_address'] = $rooms_address;
        return $reservation_data;
    }

    public function special_data($special_offer){
        if(isset($special_offer)) {
            $rooms  = $special_offer->rooms->only(['id','name']);
            $currency = $special_offer->currency->only(['id','symbol','price','session_code']);  
            $special_offer = $special_offer->only(['id','reservation_id','dates','number_of_guests','is_booked','price','avablity']);
            $relations = ['rooms'=>$rooms,'currency'=>$currency];  
            $special_relation  = array_merge($special_offer,$relations);          
            //dd($special_relation);
            return $special_relation;   
        }
        return null;
    }


    public function GetBookingStatus($reservation_details,$host_name){
            
          $special_offer = SpecialOffer::where('reservation_id',$reservation_details->id)->orderBy('id', 'desc')->first();


          $return['special_id'] = ($special_offer['type'] == 'special_offer' || $special_offer['type'] == 'pre-approval') ? $special_offer['id'] : '';

          $return['special_offer_status'] = ($special_offer['id'] !='') ? 'Yes' : 'No';

          if($special_offer['type'] == 'pre-approval' || $reservation_details->status == 'Pre-Accepted' || $special_offer['type'] == 'special_offer'){

              if($special_offer['type'] == 'special_offer' && $reservation_details->status != 'Pre-Accepted') {
                        $data_inquiry = $this->price_calculation(
                            $special_offer['room_id'],
                            $special_offer['checkin'],
                            $special_offer['checkout'],
                            $special_offer['number_of_guests'],
                        );
                  }else{
                        $data_inquiry = $this->price_calculation(
                            $reservation_details->room_id,
                            $reservation_details->checkin,
                            $reservation_details->checkout,
                            $reservation_details->number_of_guests,
                        );
                  }

              $data_inquiry = json_decode($data_inquiry, TRUE);
              $result = $data_inquiry['status'];
              $date = date('Y-m-d');
              if($result == 'Not available'){
                  $return['booking_status'] ='Already Booked';
              }else if((isset($data_inquiry['status'])) && ($result == 'Not available')){
                  $return['booking_status'] = 'Not available';
              }else {
                  $return['booking_status'] = ($reservation_details->checkin < $date) ? 'Not available' : 'Available';
              }
          }else{
              $return['booking_status'] = 'Not Available';
          }

          $chat_details = array(
              'reservation_id'   => (string) $reservation_details->id,
              'request_user_id'  => (string) $reservation_details->host_id,
              'booking_status'   => $return['booking_status'],
              'host_user_name'   => $host_name,
              'message_status'   => $reservation_details->status,
              'room_id'          => (string) $reservation_details->room_id,
              'list_type'        => $reservation_details->list_type,
              'special_offer_id'    => (string) $return['special_id'],
              'special_offer_status'=> $return['special_offer_status'],
          );
          return $chat_details;
    }

    /**
     * Send Push Notification to Users based on their device
     *
     * @return Boolean
     */
    public function SendPushNotification($user,$data)
    {
          $device_type = $user['device_type'];
          $device_id = $user['device_id'];
          if($device_id == '') { return true; }
          $push_title   = $data['title'];
          $data         = $data;
          try {
            if($device_type == 1) {
                $this->push_notification_ios($push_title, $data, $device_id);
            }else{
                $this->push_notification_android($data, $device_id);
            }
          } catch(\Exception $e) {
              logger($e->getMessage());
          }
          return true;
    }

    // Send Push Notification for IOS Device
    public function push_notification_ios($push_tittle, $data, $device_id){          
          try {
              
              $title = 'Makent';
              $notificationBuilder = new PayloadNotificationBuilder($title);
              $notificationBuilder->setBody($push_tittle)->setSound('default');

              $dataBuilder = new PayloadDataBuilder();
              $dataBuilder->addData(['custom' => $data]);

              $optionBuilder = new OptionsBuilder();
              $optionBuilder->setTimeToLive(15);

              $notification = $notificationBuilder->build();
              $data = $dataBuilder->build();
              $option = $optionBuilder->build();
              $downstreamResponse = FCM::sendTo($device_id, $option, $notification, $data);
          } 
          catch(\Exception $e){
                logger('push notification error : '.$e->getMessage());
            }
    }

    // Send Push Notification for Andriod Device
    public function push_notification_android($data, $device_id) {
        try{

            $dataBuilder = new PayloadDataBuilder();
            $dataBuilder->addData(['custom' => $data]);

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(15);

            $data = $dataBuilder->build();
            $option = $optionBuilder->build();
            $downstreamResponse = FCM::sendTo($device_id, $option, null, $data);

        }
        catch(\Exception $e) {
          logger('push notification error : '.$e->getMessage());
       }

    }

  /**
   * Common Function for Price Calculation
   *
   * @param int $room_id   Room Id
   * @param int $checkin   CheckIn Date
   * @param int $checkout   CheckOut Date
   * @param int $guest_count   Guest Count
   * @param int $special_offer_id   Special Offer Id (Optional)
   * @param int $change_reservation   Dummy string to identify Change reservation or not (Optional)
   * @return json   Calculation Result
   */
  public function price_calculation($room_id, $checkin, $checkout, $guest_count, $special_offer_id = '', $change_reservation='', $reservation_id ='')
  {
    $from                               = new DateTime($checkin);
    $to                                 = new DateTime($checkout);
    $date1                              = date('Y-m-d', strtotime($checkin));
    $enddate                            = date('Y-m-d', strtotime($checkout));
    $date2                              = date('Y-m-d',(strtotime ( '-1 day' , strtotime ($enddate ) ) ));
    $days                               = $this->get_days($date1, $date2 );
    $total_nights                       = $to->diff($from)->format("%a");
    $today                              = new DateTime(date('Y-m-d'));
    $booking_date_diff                  = $from->diff($today)->format("%a")+1;
    
    $rooms_details                      = Rooms::find($room_id);
    $rooms_price                        = $rooms_details->rooms_price;
    $calendar_result                    = Calendar::where(['room_id' => $room_id])->whereIn('date', $days)->get();

    $service_fee_percentage             = Fees::find(1)->value;
    $host_fee_percentage                = Fees::find(2)->value;

    $min_service_fee1             = Fees::find(8)->value;
    $fee_currency                 = Fees::find(9)->value;


    $result['status']                   = 'Available';
    $result['error']                    = '';
    $result['total_nights']             = $total_nights;
    $result['per_night']                = $rooms_price->night;
    $result['rooms_price']              = $rooms_price->night;
    $result['total_night_price']        = 0;
    $result['base_rooms_price']         = $rooms_price->night;
    $result['night_price']              = 0;
    $result['additional_guest']         = 0;
    $result['security_fee']             = 0;
    $result['cleaning_fee']             = 0;
    $result['service_fee']              = 0;
    $result['host_fee']                 = 0;
    $result['coupon_code']              = 0;
    $result['coupon_amount']            = 0;
    $result['currency']                 = 0;
    $result['subtotal']                 = 0;
    $result['total']                    = 0;
    $result['special_offer']            = '';
    $result['payout']                   = 0;

    $result['length_of_stay_type']      = Null;
    $result['length_of_stay_discount']  = 0;
    $result['length_of_stay_discount_price']= 0;
    $result['booked_period_type']       = Null;
    $result['booked_period_discount']   = 0;
    $result['booked_period_discount_price'] = 0;
    $min_service_fee    = 0;

    $minimum_stay                       = Null;
    $maximum_stay                       = Null;
    $pre_reserved_days                  = [];

    if($total_nights < 1) {
      $result['status'] = 'Not available';
      return json_encode($result);
    }
    if($change_reservation) {
      $pre_reservation          = Reservation::find($change_reservation);
      if($pre_reservation)
      {
        $reservation_checkin  = date('Y-m-d', strtotime($reservation_checkin));
        $reserve_date         = date('Y-m-d', strtotime($reservation_checkout));
        $reservation_checkout = date('Y-m-d', (strtotime ( '-1 day' , strtotime ($reserve_date ) ) ) );
        $pre_reserved_days    = $this->get_days($reservation_checkin, $reservation_checkout );
      }
    }

    if($rooms_details->is_shared == 'Yes')
    {
      $spots_booked = $calendar_result->max('spots_booked');

      $maximum_spots_can_book = $rooms_details->accommodates - $spots_booked;
      $result['guest_available'] =  $maximum_spots_can_book;
      if(($guest_count > $maximum_spots_can_book) && $maximum_spots_can_book > 0)
      {
        $result['status'] = 'Not available';
        $result['error']  = trans('messages.shared_rooms.maximum_spots_error',['count' => $maximum_spots_can_book]);
      }
    }

    foreach($days as $date) {
      $calendar_data = $calendar_result->where('date', $date)->first();
      $status = $calendar_data ? $calendar_data->isNotAvailable($guest_count) : false;

      if($status && !in_array($date, $pre_reserved_days)) {
        $result['status'] = 'Not available';
      }
      else {
        $price = $rooms_price->night;
        
        if($rooms_price->weekend != 0) {
          if((date('N',strtotime($date))==5 || date('N',strtotime($date))==6)) {
            $price = $rooms_price->weekend;
          }
        }
        $price =  $calendar_data ? $calendar_data->session_currency_price : $price;
        
        $result['total_night_price'] += $price;
      }
    }
    if($result['status'] == 'Not available') {
      return json_encode($result);
    }

    $availability_rules       = $rooms_details->availability_rules()->where(function($query) use($enddate, $date1){
      $query->where('start_date', '<=', $date1)->where('end_date', '>=', $date1);
    })->orderBy('id', 'desc')->get();

    if($availability_rules->count() > 0) {
      $custom_availability_rules = $availability_rules->where('type', 'custom')->first();
      $month_availability_rules = $availability_rules->where('type', 'month')->first();
      if($custom_availability_rules) {
        $minimum_stay = $custom_availability_rules->minimum_stay;
        $maximum_stay = $custom_availability_rules->maximum_stay;
      }
      else {
        $minimum_stay = $month_availability_rules->minimum_stay;
        $maximum_stay = $month_availability_rules->maximum_stay;
      }
    }
    else {
      $minimum_stay = $rooms_price->minimum_stay;
      $maximum_stay = $rooms_price->maximum_stay; 
    }

    $special_offer                = SpecialOffer::find($special_offer_id);
    if(@$special_offer->type != 'special_offer') {
      if($minimum_stay != null && $total_nights < $minimum_stay ) {
        $result['status'] = 'Not available';
        $result['error']  = trans('messages.rooms.minimum_stay_error',['count' => $minimum_stay]);
        return json_encode($result);
      }
      if($maximum_stay != null && $total_nights > $maximum_stay ) {
        $result['status'] = 'Not available';
        $result['error']  = trans('messages.rooms.maximum_stay_error',['count' => $maximum_stay]);
        return json_encode($result);
      }
    }

    $result['base_rooms_price']  = round($result['total_night_price'] / $total_nights);
    $result['night_price']  = $result['total_night_price'];

    $early_bird_rules     = $rooms_details->early_bird_rules->sortByDesc('period')->toArray();
    foreach($early_bird_rules as $rule) {
      if($booking_date_diff >= @$rule['period'] && @$rule['discount'] > 0) {
        $result['booked_period_type'] = @$rule['type'];
        $result['booked_period_discount'] = @$rule['discount'];
        $result['booked_period_discount_price'] = round (( @$rule['discount'] / 100 ) * ($result['night_price']));
        $result['night_price']      = $result['night_price'] - $result['booked_period_discount_price'];
        break;
      }
    }

    if($result['booked_period_type'] == Null) {
      $last_min_rules     = $rooms_details->last_min_rules->sortBy('period')->toArray();
      foreach($last_min_rules as $rule) {
        if(@$rule['period'] >= $booking_date_diff && @$rule['discount'] > 0) {
          $result['booked_period_type'] = @$rule['type'];
          $result['booked_period_discount'] = @$rule['discount'];
          $result['booked_period_discount_price'] = round (( @$rule['discount'] / 100 ) * ($result['night_price']));
          $result['night_price']      = $result['night_price'] - $result['booked_period_discount_price'];
          break;
        }
      }
    }
    
    $length_of_stay_rules   = $rooms_details->length_of_stay_rules;
    $length_of_stay_rules = $length_of_stay_rules->sortByDesc('period')->toArray();

    foreach($length_of_stay_rules as $rule) {
      if($total_nights >= @$rule['period'] && @$rule['discount'] > 0) {
        $result['length_of_stay_type'] = @$rule['period'] == 7 ? 'weekly' : ( @$rule['period'] == 28 ? 'monthly' : 'custom');
        $result['length_of_stay_discount'] = @$rule['discount'];
        $result['length_of_stay_discount_price'] = round (( @$rule['discount'] / 100 ) * ($result['night_price']));
        $result['night_price']      = $result['night_price'] - $result['length_of_stay_discount_price'];
        break;
      }
    }
    $result['per_night']          = round($result['night_price'] / $total_nights);

    if($guest_count > $rooms_price->guests) {
      $additional_guest_count     = $guest_count - $rooms_price->guests;
      $result['additional_guest'] = $additional_guest_count * $rooms_price->additional_guest * $total_nights; // Additional guest fee is calculated per night
    }
    if($rooms_price->security) {
      $result['security_fee']     = $rooms_price->security;
    }
    if($rooms_price->cleaning) {
      $result['cleaning_fee']     = $rooms_price->cleaning;
    }

    if($min_service_fee1)
    {
      $min_service_fee  = $this->currency_convert($fee_currency,'',$min_service_fee1);
    }

    $result['service_fee']        = round( ($service_fee_percentage / 100) * ($result['night_price'] + $result['additional_guest'] + $result['cleaning_fee']) );

    if($result['service_fee']<$min_service_fee && $service_fee_percentage)
    {
      $result['service_fee'] = $min_service_fee;
    }

    $result['host_fee']           = ceil( ($host_fee_percentage / 100) * ($result['night_price'] + $result['additional_guest'] + $result['cleaning_fee']) );

    $result['subtotal']           = $result['night_price'] + $result['additional_guest'] + $result['cleaning_fee'];
    $result['rooms_price']        = round($result['subtotal'] / $total_nights);
    $result['currency']           = $rooms_price->code;

   

    $reservation                  = Reservation::find($reservation_id);
    if($reservation)  {
      $result['additional_guest'] = $reservation->additional_guest;
      $result['security_fee']     = $reservation->security;
      $result['cleaning_fee']     = $reservation->cleaning;
      $result['coupon_amount']    = $reservation->coupon_amount;
      $result['total_night_price']= $reservation->base_per_night * $reservation->nights;
      $result['base_rooms_price'] = $reservation->base_per_night;
      $result['night_price']      = $reservation->per_night * $reservation->nights;
      $result['per_night']        = $reservation->per_night;
      $result['rooms_price']      = round($reservation->subtotal / $reservation->nights);
      $result['length_of_stay_type']           = $reservation->length_of_stay_type;
      $result['length_of_stay_discount']       = $reservation->length_of_stay_discount;
      $result['length_of_stay_discount_price'] = $reservation->length_of_stay_discount_price;
      $result['booked_period_type']            = $reservation->booked_period_type;
      $result['booked_period_discount']        = $reservation->booked_period_discount;
      $result['booked_period_discount_price']  = $reservation->booked_period_discount_price;
      $result['total_nights']     = $reservation->nights;
      $result['service_fee']      = $reservation->service;
      $result['host_fee']         = $reservation->host_fee;
      $result['subtotal']         = $reservation->subtotal;
      $result['special_offer']    = $reservation->special_offer_id;
      $result['payout']           = $reservation->payout;
      $result['currency']         = $reservation->currency->code;
    }

    $special_offer                = SpecialOffer::find($special_offer_id);
    if($special_offer && @$special_offer->type == 'special_offer') {
      $result['special_offer']    = "yes";
      $result['total_night_price']= $special_offer->price;
      $result['per_night']        = round( $special_offer->price/$total_nights );
      $result['rooms_price']      = round( $special_offer->price/$total_nights );
      $result['base_rooms_price'] = round( $special_offer->price/$total_nights );
      $result['service_fee']      = round(($service_fee_percentage / 100) * $special_offer->price);

      if($result['service_fee']<$min_service_fee && $service_fee_percentage)
      {
        $result['service_fee'] = $min_service_fee;
      }

      $result['host_fee']         = ceil(($host_fee_percentage / 100) * $special_offer->price);
    
      $result['subtotal']         = $special_offer->price;
      $result['length_of_stay_type']           = null;
      $result['length_of_stay_discount']       = 0;
      $result['length_of_stay_discount_price'] = 0;
      $result['booked_period_type']            = null;
      $result['booked_period_discount']        = 0;
      $result['booked_period_discount_price']  = 0;
    }

    $coupon_amount_total          = $result['subtotal'] + $result['service_fee'];
    if(Session::get('coupon_code')) {
      $coupon_code                = Session::get('coupon_code');
      if($coupon_code == 'Travel_Credit')
      {
        $coupon_amount            = Session::get('coupon_amount');
        $result['coupon_amount']  = ($coupon_amount_total >= $coupon_amount) ? $coupon_amount : $coupon_amount_total;
      }
      else 
      {
        $coupon_details           = CouponCode::where('coupon_code', $coupon_code)->first();
        if($coupon_details) {
          $code                   = Session::get('currency');
          $coupon_amount = $this->currency_convert($coupon_details->currency_code,$code,$coupon_details->amount);
          $result['coupon_amount']  = ($coupon_amount_total >= $coupon_amount) ? $coupon_amount : $coupon_amount_total;
        }
      }
      $result['coupon_code']      = $coupon_code;
    }

    $result['total']              = ( $result['subtotal'] + $result['service_fee'] ) - $result['coupon_amount'];
    $payment_total                = ( $result['subtotal'] + $result['service_fee'] ) - $result['coupon_amount'];
    $result['payment_total']      = $payment_total > 0 ? $payment_total : 0;
    $result['payout']             = $result['total'] - $result['service_fee'] - $result['host_fee'];

    return json_encode($result);
  }

  public function price_calculation1($room_id,$sub_room_id='', $checkin, $checkout, $guest_count, $special_offer_id = '', $change_reservation='', $reservation_id ='',$number_of_rooms='',$partial_check='')
  {
    
    $from                               = new DateTime($checkin);
    $to                                 = new DateTime($checkout);
    $date1                              = date('Y-m-d', strtotime($checkin));
    $enddate                            = date('Y-m-d', strtotime($checkout));
    $date2                              = date('Y-m-d',(strtotime ( '-1 day' , strtotime ($enddate ) ) ));
    $days                               = $this->get_days($date1, $date2 );
    $total_nights                       = $to->diff($from)->format("%a");
    $today                              = new DateTime(date('Y-m-d'));
    $booking_date_diff                  = $from->diff($today)->format("%a")+1;
    
    $rooms_details                      = Rooms::find($room_id);

    if($rooms_details->type=='Multiple'){
      
      if(!count($sub_room_id) || !$sub_room_id){
        $result['status'][0]                  = 'Not available';
        $result['error'][0] = '';
        return json_encode($result);
      }
      $in = 0;
      $sub_room_id1 = [];
      $number_of_rooms1 = [];
      $guest_count1 = [];
      foreach ($sub_room_id as $key => $value) {
        
        if($value){
          $sub_room_id1[$in] = $value;
          $number_of_rooms1[$in] = $number_of_rooms[$key];
          $guest_count1[$in] = $guest_count[$key];
          $in++;
        }
      }

      $sub_room_id = $sub_room_id1;
      $number_of_rooms = $number_of_rooms1;
      $guest_count = $guest_count1;
    }


    if($rooms_details->type=='Multiple' && count($sub_room_id)){

      foreach ($sub_room_id as $key => $value) {
        $multiple_rooms[$key]                     = MultipleRooms::find($value);
        $calendar_result[$key]                   = Calendar::where(['room_id' => $room_id])->where('multiple_room_id',$value)->whereIn('date',$days)->get();
      }
      
      $rooms_price                        = $multiple_rooms;
      $rooms_details                      = $multiple_rooms;
      
      $room_status                        = 'Multiple';
    }
    else{
      $rooms_price                        = $rooms_details->rooms_price;
      $calendar_result                    = Calendar::where(['room_id' => $room_id])->whereIn('date', $days)->get();
      $room_status                        = 'Single';
    }
    
    $service_fee_percentage             = Fees::find(1)->value;
    $host_fee_percentage                = Fees::find(2)->value;

    $result['total_nights']             = $total_nights;
    // dd($sub_room_id);
    if($room_status=='Multiple'){

      if($sub_room_id==''){
        $result['status'][0]                   = 'Not available';
        $result['error'][0]                    = trans('messages.rooms.sub_rooms_error');
      }
      $result['status_room']        = 'Multiple';
      foreach ($rooms_price as $key => $value) {
        $result['status'][$key]                   = 'Available';
        $result['error'][$key]                    = '';
        $result['per_night'][$key]                = $value->night;
        $result['rooms_price'][$key]              = $value->night;
        $result['base_rooms_price'][$key]         = $value->night;
        $result['number_of_rooms'][$key]          =  @$number_of_rooms[$key];
        $result['number_of_guests'][$key]          =  @$guest_count[$key];
        $result['total_night_price']        = [];
        $result['night_price']              = [];
        $result['length_of_stay_type']      = [];
        $result['length_of_stay_discount']  = [];
        $result['length_of_stay_discount_price']= [];
        $result['booked_period_type']       = [];
        $result['booked_period_discount']   = [];
        $result['booked_period_discount_price'] = []; 
        $minimum_stay                       = [];
        $maximum_stay                       = [];
        $result['additional_guest']         = [];
        $result['security_fee']             = [];
        $result['cleaning_fee']             = [];
        $result['service_fee']              = [];
        $result['host_fee']                 = [];
        $result['currency']                 = [];
        $result['subtotal']                 = [];
      }
    }
    else{
      $result['status']                   = 'Available';
      $result['error']                    = '';
      $result['status_room']        = 'Single';
      $result['per_night']                = $rooms_price->night;
      $result['rooms_price']              = $rooms_price->night;
      $result['base_rooms_price']         = $rooms_price->night;
      $result['total_night_price']        = 0;
      $result['night_price']              = 0;
      $result['length_of_stay_type']      = Null;
      $result['length_of_stay_discount']  = 0;
      $result['length_of_stay_discount_price']= 0;
      $result['booked_period_type']       = Null;
      $result['booked_period_discount']   = 0;
      $result['booked_period_discount_price'] = 0; 
      $minimum_stay                       = Null;
      $maximum_stay                       = Null;
      $result['additional_guest']         = 0;
      $result['base_additional_guest']    = 0;
      $result['security_fee']             = 0;
      $result['cleaning_fee']             = 0;
      $result['base_cleaning_fee']        = 0;
      $result['base_security_fee']        = 0;
      $result['service_fee']              = 0;
      $result['host_fee']                 = 0;
      $result['currency']                 = 0;
      $result['subtotal']                 = 0;
    }
   
    
    $result['coupon_code']              = 0;
    $result['coupon_amount']            = 0;
    
    $result['total']                    = 0;
    $result['special_offer']            = '';
    $result['payout']                   = 0;

    $pre_reserved_days                  = [];

    if($total_nights < 1) {
      $result['status'] = 'Not available';
      return json_encode($result);
    }

    if($room_status =='Single'){
      if($guest_count > $rooms_details->accommodates){
        $result['status'] = 'Not available';
        $result['error']  = trans('messages.shared_rooms.maximum_spots_error',['count' => $rooms_details->accommodates]);
        return json_encode($result);
      }
    }
    else{
      foreach ($rooms_details as $key => $value) {
        if($guest_count[$key] > $value->accommodates * $value->number_of_rooms){
          $result['status'][$key] = 'Not available';
          $result['error'][$key]  = trans('messages.shared_rooms.maximum_spots_error',['count' => $value->accommodates]);
          return json_encode($result);
        }
      }
    }

    if($change_reservation) {
      $pre_reservation          = Reservation::find($change_reservation);
      if($pre_reservation)
      {
        $reservation_checkin  = date('Y-m-d', strtotime($reservation_checkin));
        $reserve_date         = date('Y-m-d', strtotime($reservation_checkout));
        $reservation_checkout = date('Y-m-d', (strtotime ( '-1 day' , strtotime ($reserve_date ) ) ) );
        $pre_reserved_days    = $this->get_days($reservation_checkin, $reservation_checkout );
      }
    }
    if($room_status=='Multiple'){

      $sub_room_check = count($sub_room_id) !== count(array_unique($sub_room_id));

      if($sub_room_check==true){
        foreach ($rooms_details as $key => $value) {
          $result['status'][$key] = 'Not available';
          $result['error'][$key]  = trans('messages.rooms.number_of_rooms_error');
        }
      }

      foreach ($rooms_details as $key => $value) {
        
        //if($value->is_shared == 'Yes')
        //{
          $room_count = $calendar_result[$key]->max('room_count');
          
          $maximum_spots_can_book = $value->number_of_rooms - $room_count;
          
          if((@(int)$number_of_rooms[$key] > $maximum_spots_can_book) && $maximum_spots_can_book > 0)
          {
            $result['status'][$key] = 'Not available';
            $result['error'][$key]  = trans('messages.shared_rooms.maximum_spots_error1',['count' => $maximum_spots_can_book]);
          }
        //}
      }
    }
    else{
      if($rooms_details->is_shared == 'Yes')
      {
        $spots_booked = $calendar_result->max('spots_booked');
        $maximum_spots_can_book = $rooms_details->accommodates - $spots_booked;

        if(($guest_count > $maximum_spots_can_book) && $maximum_spots_can_book > 0)
        {
          $result['status'] = 'Not available';
          $result['error']  = trans('messages.shared_rooms.maximum_spots_error',['count' => $maximum_spots_can_book]);
        }
      }
      
    }

    if($room_status=='Multiple'){
      foreach ($rooms_price as $key => $value) {
        $total_night_price = 0;
        foreach($days as $date) {
          $calendar_data = $calendar_result[$key]->where('date', $date)->where('multiple_room_id',@$sub_room_id[$key])->first();

            $status = $calendar_data ? $calendar_data->isNotRoomAvailables(@$number_of_rooms[$key]) : false;

          if($status && !in_array($date, $pre_reserved_days)) {
            $result['status'][$key] = 'Not available';
          }
          else {
            $price = $value->night;
            
            if($value->weekend != 0) {
              if((date('N',strtotime($date))==5 || date('N',strtotime($date))==6)) {
                $price = $value->weekend;
              }
            }
            $price =  $calendar_data ? $calendar_data->session_currency_price : $price;
            
            $total_night_price += $price;
            $result['total_night_price'][$key] = $total_night_price;
          }
        }
      }
    }
    else{
      foreach($days as $date) {
        $calendar_data = $calendar_result->where('date', $date)->first();
        
          $status = $calendar_data ? $calendar_data->isNotAvailable($guest_count) : false;

        if($status && !in_array($date, $pre_reserved_days)) {
          $result['status'] = 'Not available';
        }
        else {
          $price = $rooms_price->night;
          
          if($rooms_price->weekend != 0) {
            if((date('N',strtotime($date))==5 || date('N',strtotime($date))==6)) {
              $price = $rooms_price->weekend;
            }
          }
          $price =  $calendar_data ? $calendar_data->session_currency_price : $price;
          
          $result['total_night_price'] += $price;
        }
      }
    }
    

    if($room_status=='Multiple'){
      foreach ($rooms_price as $key => $value) {
        if($result['status'][$key] == 'Not available') {
          return json_encode($result);
        }
      }
    }
    else{
      if($result['status'] == 'Not available') {
        return json_encode($result);
      }
    }
    
    if($room_status=='Multiple'){
      foreach ($rooms_details as $key => $value) {
        $availability_rules       = $value->availability_rules()->where(function($query) use($enddate, $date1){
        $query->where('start_date', '<=', $date1)->where('end_date', '>=', $date1);
        })->orderBy('id', 'desc')->get();

        if($availability_rules->count() > 0) {
          $custom_availability_rules = $availability_rules->where('type', 'custom')->first();
          $month_availability_rules = $availability_rules->where('type', 'month')->first();
          if($custom_availability_rules) {
            $minimum_stay[$key] = $custom_availability_rules->minimum_stay;
            $maximum_stay[$key] = $custom_availability_rules->maximum_stay;
          }
          else {
            $minimum_stay[$key] = $month_availability_rules->minimum_stay;
            $maximum_stay[$key] = $month_availability_rules->maximum_stay;
          }
        }
        else {
          $minimum_stay[$key] = $value->minimum_stay;
          $maximum_stay[$key] = $value->maximum_stay; 
        }
      }

      if(count($minimum_stay)>0){
        foreach ($minimum_stay as $key => $value) {
          $special_offer                = SpecialOffer::find($special_offer_id);
          if(@$special_offer->type != 'special_offer') {
            if($minimum_stay[$key] != null && $total_nights < $minimum_stay[$key] ) {
              $result['status'][$key] = 'Not available';
              $result['error'][$key]  = trans('messages.rooms.minimum_stay_error1',['count' => $minimum_stay[$key]]).' '.@$rooms_details[$key]->name;
              return json_encode($result);
            }
            if($maximum_stay[$key] != null && $total_nights > $maximum_stay[$key] ) {
              $result['status'][$key] = 'Not available';
              $result['error'][$key]  = trans('messages.rooms.maximum_stay_error1',['count' => $maximum_stay[$key]]).' '.@$rooms_details[$key]->name;
              return json_encode($result);
            }
          }
        }
      }

      if(count($result['total_night_price'])>0){
        foreach ($result['total_night_price'] as $key => $value) {
          if(@$number_of_rooms[$key]){
            $result['total_night_price'][$key] = $result['total_night_price'][$key] * @$number_of_rooms[$key];
            $result['base_rooms_price'][$key]  = round($result['total_night_price'][$key] / ($total_nights * @$number_of_rooms[$key]));
          }
          else{
            $result['base_rooms_price'][$key]  = round($result['total_night_price'][$key] / ($total_nights));
          }
          $result['night_price'][$key]  = $result['total_night_price'][$key];
        }
      }

    }
    else{
      $availability_rules       = $rooms_details->availability_rules()->where(function($query) use($enddate, $date1){
      $query->where('start_date', '<=', $date1)->where('end_date', '>=', $date1);
      })->orderBy('id', 'desc')->get();

      if($availability_rules->count() > 0) {
        $custom_availability_rules = $availability_rules->where('type', 'custom')->first();
        $month_availability_rules = $availability_rules->where('type', 'month')->first();
        if($custom_availability_rules) {
          $minimum_stay = $custom_availability_rules->minimum_stay;
          $maximum_stay = $custom_availability_rules->maximum_stay;
        }
        else {
          $minimum_stay = $month_availability_rules->minimum_stay;
          $maximum_stay = $month_availability_rules->maximum_stay;
        }
      }
      else {
        $minimum_stay = $rooms_price->minimum_stay;
        $maximum_stay = $rooms_price->maximum_stay; 
      }

      $special_offer                = SpecialOffer::find($special_offer_id);
      if(@$special_offer->type != 'special_offer') {
        if($minimum_stay != null && $total_nights < $minimum_stay ) {
          $result['status'] = 'Not available';
          $result['error']  = trans('messages.rooms.minimum_stay_error',['count' => $minimum_stay]);
          return json_encode($result);
        }
        if($maximum_stay != null && $total_nights > $maximum_stay ) {
          $result['status'] = 'Not available';
          $result['error']  = trans('messages.rooms.maximum_stay_error',['count' => $maximum_stay]);
          return json_encode($result);
        }
      }

      $result['base_rooms_price']  = round($result['total_night_price'] / $total_nights);
      $result['night_price']  = $result['total_night_price'];

    }
    
    if($room_status=='Multiple'){

      foreach ($rooms_details as $key => $value) {
        $result['booked_period_type'][$key] = null;
        $result['booked_period_discount'][$key] = 0;
        $result['booked_period_discount_price'][$key] = 0; 
        $result['length_of_stay_type'][$key] = null;
        $result['length_of_stay_discount'][$key] = 0;
        $result['length_of_stay_discount_price'][$key] = 0;
        $early_bird_rules     = $value->early_bird_rules->sortByDesc('period')->toArray();
        foreach($early_bird_rules as $rule) {
          if($booking_date_diff >= @$rule['period'] && @$rule['discount'] > 0) {
            $result['booked_period_type'][$key] = @$rule['type'];
            $result['booked_period_discount'][$key] = @$rule['discount'];
            $result['booked_period_discount_price'][$key] = round (( @$rule['discount'] / 100 ) * ($result['night_price'][$key]));
            $result['night_price'][$key]      = $result['night_price'][$key] - $result['booked_period_discount_price'][$key];
            break;
          }
        }

        if(@$result['booked_period_type'][$key] == []) {
          $last_min_rules     = $value->last_min_rules->sortBy('period')->toArray();
          foreach($last_min_rules as $rule) {
            if(@$rule['period'] >= $booking_date_diff && @$rule['discount'] > 0) {
              $result['booked_period_type'][$key] = @$rule['type'];
              $result['booked_period_discount'][$key] = @$rule['discount'];
              $result['booked_period_discount_price'][$key] = round (( @$rule['discount'] / 100 ) * ($result['night_price'][$key]));
              $result['night_price'][$key]      = $result['night_price'][$key] - $result['booked_period_discount_price'][$key];
              break;
            }
          }
        }
        
        $length_of_stay_rules   = $value->length_of_stay_rules;
        $length_of_stay_rules = $length_of_stay_rules->sortByDesc('period')->toArray();

        foreach($length_of_stay_rules as $rule) {
          if($total_nights >= @$rule['period'] && @$rule['discount'] > 0) {
            $result['length_of_stay_type'][$key] = @$rule['period'] == 7 ? 'weekly' : ( @$rule['period'] == 28 ? 'monthly' : 'custom');
            $result['length_of_stay_discount'][$key] = @$rule['discount'];
            $result['length_of_stay_discount_price'][$key] = round (( @$rule['discount'] / 100 ) * ($result['night_price'][$key]));
            $result['night_price'][$key]      = $result['night_price'][$key] - $result['length_of_stay_discount_price'][$key];
            break;
          }
        }

        $result['per_night'][$key]          = round($result['night_price'][$key] / $total_nights);
      }
    }
    else{
      $early_bird_rules     = $rooms_details->early_bird_rules->sortByDesc('period')->toArray();
      foreach($early_bird_rules as $rule) {
        if($booking_date_diff >= @$rule['period'] && @$rule['discount'] > 0) {
          $result['booked_period_type'] = @$rule['type'];
          $result['booked_period_discount'] = @$rule['discount'];
          $result['booked_period_discount_price'] = round (( @$rule['discount'] / 100 ) * ($result['night_price']));
          $result['night_price']      = $result['night_price'] - $result['booked_period_discount_price'];
          break;
        }
      }

      if($result['booked_period_type'] == Null) {
        $last_min_rules     = $rooms_details->last_min_rules->sortBy('period')->toArray();
        foreach($last_min_rules as $rule) {
          if(@$rule['period'] >= $booking_date_diff && @$rule['discount'] > 0) {
            $result['booked_period_type'] = @$rule['type'];
            $result['booked_period_discount'] = @$rule['discount'];
            $result['booked_period_discount_price'] = round (( @$rule['discount'] / 100 ) * ($result['night_price']));
            $result['night_price']      = $result['night_price'] - $result['booked_period_discount_price'];
            break;
          }
        }
      }
      
      $length_of_stay_rules   = $rooms_details->length_of_stay_rules;
      $length_of_stay_rules = $length_of_stay_rules->sortByDesc('period')->toArray();

      foreach($length_of_stay_rules as $rule) {
        if($total_nights >= @$rule['period'] && @$rule['discount'] > 0) {
          $result['length_of_stay_type'] = @$rule['period'] == 7 ? 'weekly' : ( @$rule['period'] == 28 ? 'monthly' : 'custom');
          $result['length_of_stay_discount'] = @$rule['discount'];
          $result['length_of_stay_discount_price'] = round (( @$rule['discount'] / 100 ) * ($result['night_price']));
          $result['night_price']      = $result['night_price'] - $result['length_of_stay_discount_price'];
          break;
        }
      }

      $result['per_night']          = round($result['night_price'] / $total_nights);
    }
    
    foreach ($rooms_price as $key => $value) {
      $result['base_additional_guest'][$key] = 0;
      $result['additional_guest'][$key] = 0;
      if(@$guest_count[$key] > $value->guests) {
        $additional_guest_count     = @$guest_count[$key] - ($number_of_rooms[$key] * $value->guests);
        $additional_guest = $additional_guest_count * $value->additional_guest * $total_nights; // Additional guest fee is calculated per night
        $result['additional_guest'][$key] = $additional_guest < 0 ? 0 : $additional_guest;
        $result['base_additional_guest'][$key] = $result['additional_guest'][$key] / $total_nights; // Additional guest fee is calculated per night
      }
      $result['security_fee'][$key]       = 0;
      if($value->security) {
        $result['security_fee'][$key]     = $value->security;
      }
      $result['base_cleaning_fee'][$key]  = 0;
      $result['cleaning_fee'][$key]  = 0;
      if($value->cleaning) {
        $result['base_cleaning_fee'][$key]= $value->cleaning;
        $result['cleaning_fee'][$key]     = $number_of_rooms[$key] * $value->cleaning;
      }

      $result['service_fee'][$key]        = round( ($service_fee_percentage / 100) * ($result['night_price'][$key] + @$result['additional_guest'][$key] + @$result['cleaning_fee'][$key]) );
      $result['host_fee'][$key]           = round( ($host_fee_percentage / 100) * ($result['night_price'][$key] + @$result['additional_guest'][$key] + @$result['cleaning_fee'][$key]) );
      $result['subtotal'][$key]           = $result['night_price'][$key] + @$result['additional_guest'][$key] + @$result['security_fee'][$key] + @$result['cleaning_fee'][$key];
      $result['rooms_price'][$key]        = round($result['subtotal'][$key] / $total_nights);
      $result['currency'][$key]           = $value->code;
    }
    
    // $partial_fee_percentage             = Fees::where('name','partial_payment')->first()->value;
    $reservation                  = Reservation::find($reservation_id);
    
    if($reservation)  {
      $partial_fee_percentage = $reservation->partial_percentage;
        if(count($reservation->multiple_reservation)>0){

          foreach ($reservation->multiple_reservation as $key => $value) {
            
            $result['additional_guest'][$key] = $value->additional_guest;
            $result['security_fee'][$key]     = $value->security;
            $result['base_cleaning_fee'][$key]= $value->cleaning / $value->number_of_rooms;
            $result['cleaning_fee'][$key]     = $value->cleaning;
            $result['coupon_amount']    = $reservation->coupon_amount;
            $result['total_night_price'][$key]= $value->base_per_night * $value->nights * $value->number_of_rooms;
            $result['base_rooms_price'][$key] = $value->base_per_night;
            $result['night_price'][$key]      = $value->per_night * $value->nights * $value->number_of_rooms;
            $result['per_night'][$key]        = $value->per_night;
            $result['rooms_price'][$key]      = round($value->subtotal / $value->nights);
            $result['length_of_stay_type'][$key]           = $value->length_of_stay_type;
            $result['length_of_stay_discount'][$key]       = $value->length_of_stay_discount;
            $result['length_of_stay_discount_price'][$key] = $value->length_of_stay_discount_price;
            $result['booked_period_type'][$key]            = $value->booked_period_type;
            $result['booked_period_discount'][$key]        = $value->booked_period_discount;
            $result['booked_period_discount_price'][$key]  = $value->booked_period_discount_price;
            $result['total_nights']    = $value->nights;
            $result['service_fee'][$key]      = $value->service;
            $result['host_fee'][$key]         = $value->host_fee;
            $result['subtotal'][$key]         = $value->subtotal;
            $result['special_offer']    = $reservation->special_offer_id;
            $result['payout']           = $reservation->payout;
            $result['currency'][$key]         = $reservation->currency->code;
          }
        }
    }

    $special_offer                = SpecialOffer::find($special_offer_id);
    if($special_offer && @$special_offer->type == 'special_offer') {
      $partial_fee_percentage = $special_offer->reservation->partial_percentage;
        $result['subtotal']    = [];
        $result['total_night_price'] = [];
        $result['per_night'] = [];
        $result['rooms_price'] = [];
        $result['base_rooms_price'] = [];
        $result['service_fee'] = [];
        $result['host_fee'] = [];
        $result['special_offer']    = "yes";
        $result['total_night_price'][0]= $special_offer->price;
        $result['per_night'[0]]        = round( $special_offer->price/$total_nights );
        $result['rooms_price'][0]      = round( $special_offer->price/$total_nights );
        $result['base_rooms_price'][0] = round( $special_offer->price/$total_nights );
        $result['service_fee'][0]      = round(($service_fee_percentage / 100) * $special_offer->price);
        $result['host_fee'][0]         = round(($host_fee_percentage / 100) * $special_offer->price);
        $result['subtotal'][0]         = $special_offer->price;
        $result['length_of_stay_type']           = [];
        $result['length_of_stay_discount']       = [];
        $result['length_of_stay_discount_price'] = [];
        $result['booked_period_type']            = [];
        $result['booked_period_discount']        = [];
        $result['booked_period_discount_price']  = [];
    }

    $coupon_amount_total          = array_sum($result['subtotal']) + array_sum($result['service_fee']);


    // $partial_fee_percentage_check             = Fees::where('name','partial_payment_check')->first()->value;
    if($partial_check!='Yes'){
      if(Session::get('coupon_code')) {
        $coupon_code                = Session::get('coupon_code');
        if($coupon_code == 'Travel_Credit')
        {
          $coupon_amount            = Session::get('coupon_amount');
          $result['coupon_amount']  = ($coupon_amount_total >= $coupon_amount) ? $coupon_amount : $coupon_amount_total;
        }
        else 
        {
          $coupon_details           = CouponCode::where('coupon_code', $coupon_code)->first();
          if($coupon_details) {
            $code                   = Session::get('currency');
            $result['coupon_amount']= $this->currency_convert($coupon_details->currency_code,$code,$coupon_details->amount);
          }
        }
        $result['coupon_code']      = $coupon_code;
      }
    }

    $result['partial_amount'] = '';
    $result['partial_amount_check'] = 'No';
    
    // $result['partial_percentage'] = $partial_fee_percentage;
    if($room_status=='Multiple'){
      $total                        = array_sum($result['subtotal']) + array_sum($result['service_fee']);
      $result['total']              = $total - $result['coupon_amount'];
      $payment_total                = $total - array_sum($result['security_fee']) - $result['coupon_amount'];
      $result['payment_total']      = $payment_total > 0 ? $payment_total : 0;
      $result['payout']             = $result['total'] - array_sum($result['service_fee']) - array_sum($result['host_fee']);

      // if($partial_fee_percentage_check=='Yes' && $partial_check=='Yes'){
      
      //   $partial_amount = round(($partial_fee_percentage / 100) * $result['total']);
      //   $service_host_total = array_sum($result['service_fee']) +  array_sum($result['host_fee']);
      //   if($partial_amount>=$service_host_total){
      //     $result['payment_total'] = round(($partial_fee_percentage / 100) * $result['payment_total']);
      //     $result['partial_amount_check'] = 'Yes';
      //     $result['partial_amount'] = $partial_amount;
      //   }

      // }
    }
    else{
      $result['total']              = ( $result['subtotal'] + $result['service_fee'] )- $result['security_fee'] - $result['coupon_amount'];
      $payment_total                = ( $result['subtotal'] + $result['service_fee'] )  - $result['security_fee'] - $result['coupon_amount'];
      $result['payment_total']      = $payment_total > 0 ? $payment_total : 0;
      $result['payout']             = $result['total'] - $result['service_fee'] - $result['host_fee'];

      if($partial_fee_percentage_check=='Yes' && $partial_check=='Yes'){
      
        $partial_amount = round(($partial_fee_percentage / 100) * $result['total']);
        if($partial_amount>=($result['service_fee'] + $result['host_fee'])){
          $result['payment_total'] = round(($partial_fee_percentage / 100) * $result['payment_total']);
          $result['partial_amount_check'] = 'Yes';
          $result['partial_amount'] = $partial_amount;
        }

      }
    }

    
    
    

    return json_encode($result);
  }  

    /**
     * Get days between two dates
     *
     * @param date $sStartDate  Start Date
     * @param date $sEndDate    End Date
     * @return array $days      Between two dates
     */
    public function get_days($sStartDate, $sEndDate)
    {           
      $aDays[]      = $sStartDate;
      $sCurrentDate = $sStartDate;  
       
      while($sCurrentDate < $sEndDate)
      {
        $sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));
        $aDays[]      = $sCurrentDate;  
      }
      
      return $aDays;  
    }

    /**
     * Currency Convert
     *
     * @param int $from   Currency Code From
     * @param int $to     Currency Code To
     * @param int $price  Price Amount
     * @return int Converted amount
     */
    public function currency_convert($from = '', $to = '', $price)
    {
      if($from == '')
      {
        if(Session::get('currency'))
           $from = Session::get('currency');
        else
           $from = Currency::where('default_currency', 1)->first()->code;
      }

      if($to == '')
      {
        if(Session::get('currency'))
           $to = Session::get('currency');
        else
           $to = Currency::where('default_currency', 1)->first()->code;
      }

      $rate = Currency::whereCode($from)->first()->rate;

      $usd_amount = $price / $rate;
      
      $session_rate = Currency::whereCode($to)->first()->rate;

      return ceil($usd_amount * $session_rate);
    }

    /**
     * Currency Rate
     *
     * @param int $from   Currency Code From
     * @param int $to     Currency Code To
     * @return int Converted Currency Rate
     */
    public function currency_rate($from, $to)
    {
      $from_rate = Currency::whereCode($from)->first()->rate;
      $to_rate = Currency::whereCode($to)->first()->rate;

      return round($from_rate / $to_rate);
    }

    /**
     * Date Convert
     *
     * @param date $date   Given Date
     * @return date Converted new date format
     */
    public function date_convert($date)
    {
      return date('Y-m-d', $this->custom1_strtotime($date));
    }

    public function custom1_strtotime($date)
    {
        if(PHP_DATE_FORMAT=="d/m/Y" || PHP_DATE_FORMAT=="m-d-Y")
        {
            $seperator=(PHP_DATE_FORMAT=="d/m/Y")? "/" : "-";
            $explode_date=explode($seperator,$date);
            if(count($explode_date)=="1")
            {
                return strtotime($date);
            }
            else
            {
                $original_date=$explode_date[1].$seperator.$explode_date[0].$seperator.$explode_date[2];  
                return strtotime($original_date);     
            }            
        }
        else
        {
            return strtotime($date);
        }
    }
    /**
     * Penalty Amount Check
     *
     * @param total $amount   Given amount
     * @return check if any penalty for this host then renturn remaining amount
     */

    public function check_host_penalty($penalty, $reservation_amount,$reservation_currency_code)
    {
      $penalty_id = '';
      $penalty_amount = '';

      if($penalty->count() > 0 )
      {
        $host_amount = $reservation_amount;
        foreach ($penalty as $pen) {
          $host_amount = $this->currency_convert($reservation_currency_code,$pen->currency_code,$host_amount); // Convert the host amount to peanlty currency to compare

          $remaining_amount = $pen->remain_amount;
          if($host_amount >= $remaining_amount)
          {
            $host_amount = $host_amount - $remaining_amount;

            $pen->remain_amount    = 0;
            $pen->status           = "Completed";
            $pen->save();

            $penalty_id .= $pen->id.',';
            $penalty_amount .= $remaining_amount.',';
          }
          else
          {
            $amount_reamining = $remaining_amount - $host_amount;

            $pen->remain_amount  = $amount_reamining;
            $pen->save();

            $penalty_id .= $pen->id.',';
            $penalty_amount .= $amount_reamining.',';

            $host_amount = 0;
          }
          $host_amount = $this->currency_convert($pen->currency_code,$reservation_currency_code,$host_amount); // Revert the host amount to reservation currency code
          
        }
        $penalty_id     = rtrim($penalty_id, ',');
        $penalty_amount = rtrim($penalty_amount, ',');
      }
      else
      {
        $host_amount = $reservation_amount;
        $penalty_id     = 0;
        $penalty_amount = 0;
      }

      $result['host_amount']     = $host_amount;
      $result['penalty_id']      = $penalty_id;
      $result['penalty_amount']  = $penalty_amount;

      return $result;
    }

    public function revert_travel_credit($reservation_id) {

      $applied_referrals = AppliedTravelCredit::whereReservationId($reservation_id)->get();

      foreach($applied_referrals as $row) {
        $referral = Referrals::find($row->referral_id);

        if($row->type == 'main')
          $referral->credited_amount = $referral->credited_amount + $this->currency_convert($row->currency_code, $referral->currency_code, $row->original_amount);
        else
          $referral->friend_credited_amount = $referral->friend_credited_amount + $this->currency_convert($row->currency_code, $referral->currency_code, $row->original_amount);
        
        $referral->save();

        $applied_referrals = AppliedTravelCredit::find($row->id)->delete();
      }

    }


    /**
     * To process the payouts and refunds based on reservations
     *
     * @param App\Models\Reservation $reservation
     * @param Int $guest_refundable_amount 
     * @param Int $host_payout_amount 
     */
    public function payout_refund_processing($reservation, $guest_refundable_amount = 0, $host_payout_amount = 0, $host_penalty_amount = 0)
    {
      // Create new / Find Payout row for Guest & Host
      $guest_check_data = array(
        'user_id' => $reservation->user_id,
        'reservation_id' => $reservation->id,
        );
      $guest_refund = Payouts::firstOrNew($guest_check_data);

      $host_check_data = array(
        'user_id' => $reservation->host_id,
        'reservation_id' => $reservation->id,
        );
      $host_payout = Payouts::firstOrNew($host_check_data);

      // Revert already applied penalty for this reservation
      if(@$host_payout->penalty_id != 0 && @$host_payout->penalty_id != '')
      {
        $penalty_id = explode(",",$host_payout->penalty_id);
        $penalty_amt = explode(",",$host_payout->penalty_amount);
        $i =0;
        foreach ($penalty_id as $row) 
        {
          $old_amt = HostPenalty::where('id',$row)->get();
          $currency_code = $host_payout->currency_code;
          if(@$penalty_amt[$i]) {
            $penalty_amt = currency_convert($currency_code, $old_amt[0]->currency_code , $penalty_amt[$i]);
            $upated_amt = $old_amt[0]->remain_amount + $penalty_amt;
            HostPenalty::where('id',$row)->update(['remain_amount' => $upated_amt,'status' => 'Pending' ]); 
          }
          $i++;
        }
      }

      // Process and Save guest refund amount
      if($guest_refundable_amount > 0)
      {
        if(!@$guest_refund->id)
        {
          $guest_refund->reservation_id = $reservation->id;
          $guest_refund->room_id        = $reservation->room_id;
          $guest_refund->user_id        = $reservation->user_id;
          $guest_refund->user_type      = 'guest';
          $guest_refund->currency_code  = $reservation->currency_code;
          $guest_refund->status         = 'Future';
          $guest_refund->save();
        }
        $guest_refund->currency_code  = $reservation->currency_code;
        $guest_refund->amount         = $guest_refundable_amount;
        $guest_refund->currency_code  = $reservation->currency_code;
        $guest_refund->save();
      }

      // Save the host penalty amount for this reservation
      if($host_penalty_amount > 0)
      {
        $penalty = new HostPenalty;
        $penalty->reservation_id = $reservation->id;
        $penalty->room_id        = $reservation->room_id;
        $penalty->user_id        = $reservation->host_id;
        $penalty->remain_amount  = $host_penalty_amount;
        $penalty->amount         = $host_penalty_amount;
        $penalty->currency_code  = $reservation->currency_code;
        $penalty->status         = 'Pending';
        $penalty->save();
      }

      // Process and Save the host payout amount with host penalty
      if($host_payout_amount > 0)
      {
        // Apply penaly for final host payout amount
        $penalty = HostPenalty::where('user_id',$reservation->host_id)->where('remain_amount','!=',0)->get();
        $penalty_result = $this->check_host_penalty($penalty,$host_payout_amount,$reservation->currency_code);
        $host_amount    = $penalty_result['host_amount'];
        $penalty_id     = $penalty_result['penalty_id'];
        $penalty_amount = $penalty_result['penalty_amount'];

        if(!@$host_payout->id)
        {
          $host_payout->reservation_id = $reservation->id;
          $host_payout->room_id        = $reservation->room_id;
          $host_payout->user_id        = $reservation->host_id;
          $host_payout->user_type      = 'host';
          $host_payout->currency_code  = $reservation->currency_code;
          $host_payout->status         = 'Future';
          $host_payout->save();
        }
        $host_payout->currency_code  = $reservation->currency_code;
        $host_payout->amount         = $host_amount;
        $host_payout->penalty_amount = $host_payout_amount - $host_amount;
        $host_payout->penalty_id     = $penalty_id;
        $host_payout->save();
      }
      else
      {
        if($host_payout)
          $host_payout->delete();
      }
    }
}
