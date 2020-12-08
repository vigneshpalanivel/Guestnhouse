<?php

/**
 * Trips Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Trips
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Http\Helper\PaymentHelper;
use App\Http\Start\Helpers;
use App\Models\Calendar;
use App\Models\Currency;
use App\Models\Fees;
use App\Models\Messages;
use App\Models\ProfilePicture;
use App\Models\Reservation;
use App\Models\HostExperienceCalendar;
use App\Models\Rooms;
use Auth;
use DB;
use DateTime;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class TripsController extends Controller {
	/**
	 * Load Current Trips page.
	 *
	 * @return view Current Trips File
	 */
	protected $helper; // Global variable for Helpers instance

	protected $payment_helper; // Global variable for PaymentHelper instance

	public function __construct(PaymentHelper $payment) {
		$this->payment_helper = $payment;
		$this->helper = new Helpers;
	}


	/**
	 *Display trips type
	 *
	 * @param  Get method inputs
	 * @return Response in Json
	 */
	public function trips_type(Request $request) {

		$user = JWTAuth::parseToken()->authenticate();
		//get preaccepted reservation list
		$preaccepted = Reservation::where('status', '=', 'Pre-Accepted')

			->where('date_check', '!=', 'No')->get();

		foreach ($preaccepted as $chck_pre) {
			$chck_pre->avablity;
		}
		//get pending trips detials
		$data['pending_trips'] = Reservation::with('users', 'rooms')->where('type', '!=', 'contact')
			->where(function ($query) {
				$query->where('status', 'Pending')->orwhere('status', 'Pre-Accepted');
			})
			->where('checkin', '>=', date('Y-m-d'))
			->where('user_id', $user->id)->get();
		//get current trips details
		$data['current_trips'] = Reservation::with('users', 'rooms')->where(function ($query) {
			$query->where(function ($query) {
				$query->where('checkin', '>=', date('Y-m-d'))->where('checkout', '<=', date('Y-m-d'));
			})->orWhere(function ($query) {
				$query->where('checkin', '<=', date('Y-m-d'))->where('checkout', '>=', date('Y-m-d'));
			});
		})->where('status', '!=', 'Pending')->where('status', '!=', 'Pre-Accepted')->where('type', '!=', 'contact')->where('user_id', $user->id)->get();
		//get uploming trips details
		$data['upcoming_trips'] = Reservation::with('users', 'rooms')->where('checkin', '>', date('Y-m-d'))->where('type', '!=', 'contact')->where('status', '!=', 'Pre-Accepted')->where('status', '!=', '')->where('status', '!=', 'Pending')->where('user_id', $user->id)->get();
		//previous trips details
		$data['previous_trips'] = Reservation::with('users', 'rooms')->where('checkout', '<', date('Y-m-d'))->where('checkout', '!=', '0000-00-00')->where('user_id', $user->id)->get();

		//check trips count greater then 0
		if ($data['pending_trips']->count() > 0) {

			$trips[] = trans('messages.your_trips.pending_trips');
			$Trips_type_text[] = 'Pending Trips';
			$trips_count[] = $data['pending_trips']->count();

		}
		if ($data['current_trips']->count() > 0) {

			$trips[] = trans('messages.your_trips.current_trips');
			$Trips_type_text[] = 'Current Trips';
			$trips_count[] = $data['current_trips']->count();

		}
		if ($data['upcoming_trips']->count() > 0) {
			$trips[] = trans('messages.your_trips.upcoming_trips');
			$Trips_type_text[] = 'Upcoming Trips';
			$trips_count[] = $data['upcoming_trips']->count();
		}
		if ($data['previous_trips']->count() > 0) {
			$trips[] = trans('messages.your_trips.previous_trips');
			$Trips_type_text[] = 'Previous Trips';
			$trips_count[] = $data['previous_trips']->count();
		}
		if (@$trips == null) {
			return response()->json([
				'success_message' => trans('messages.api.no_trips_found'),
				'status_code' => '0',
			]);
		}

		return response()->json([
			'success_message' => trans('messages.api.trip_type_listed_success'),
			'status_code' => '1',
			'Trips_type_text' => $Trips_type_text,
			'Trips_type' => $trips,
			'Trips_count' => $trips_count,
		]);
	}


	public function instant_trip_details(Request $request){
		
		$reservation = Reservation::find($request->reservation_id);
		if($reservation->status == 'Pending'){
			$expireCheck = resolve('App\Http\Controllers\ReservationController'); 
			$expireCheck->expireReservation($reservation->id);
		}

		$today = date('Y-m-d');
		$reservation = Reservation::with(['users', 'rooms' => function ($query) { $query->with('rooms_address');
		}, 'host_users'])->find($request->reservation_id);

		$user = JWTAuth::parseToken()->authenticate();
		$user_type = ($reservation->user_id == $user->id) ? 'Guest' : 'Host';

		if((($reservation->type !='contact' && $reservation->status=='Pending'|| $reservation->status=='Pre-Accepted') || $reservation->status=='Pre-Approved') && $reservation->checkin >= $today){
			$reservation_status = 'pending_trips';
		}
		else if($reservation->type !='contact' && $reservation->status!='Pending' && $reservation->status !='Pre-Accepted' && ($reservation->checkin >= $today && $reservation->checkout <= $today || $reservation->checkin <= $today && $reservation->checkout >= $today)){
			$reservation_status = 'current_trips';
		}
		else if($reservation->type !='contact' && $reservation->status!='Pre-Accepted' && $reservation->status!='' && $reservation->status!='Pending' && $reservation->checkin > $today ){
			$reservation_status = 'upcoming_trips';
		}
		else if($reservation->checkout < $today && $reservation->checkout!='0000-00-00'){
			$reservation_status = 'previous_trips';
		}

		if (!isset($reservation_status)) {
			return response()->json([
				'success_message' => 'Reservation Not Found',
				'status_code' => '0',
			]);
		}

		$expire_timer = '';
		$result_data = $reservation;
		if ($reservation_status == 'pending_trips') {
			$from_time = strtotime($result_data->created_at_timer);
			$to_time = strtotime(date('Y/m/d H:i:s'));
			$diff = abs($from_time - $to_time);
			$expire_timer = sprintf('%02d:%02d:%02d', ($diff / 3600), ($diff / 60 % 60), $diff % 60);

			//$room_location[] = $result_data->rooms->rooms_address->country_name;

			$date = date('Y-m-d');
			if ($result_data->checkin < $date) {
				if ($result_data->status == 'Pre-Accepted') {
						@$booking_status = 'Already Booked';
					}
			} else {
					if($result_data->status == 'Pre-Accepted' || $result_data->status == 'Pre-Approved'){
						@$booking_status = $this->get_status(
							$result_data->room_id,
							$result_data->checkin,
							$result_data->checkout,
							$result_data->number_of_guests
						);
					} else { @$booking_status = ''; }
			}
		}

		$room_location = array();
		
		if ($result_data->rooms->rooms_address->address_line_1 != '') {
			$room_location[] = $result_data->rooms->rooms_address->address_line_1;
		}
		if ($result_data->rooms->rooms_address->address_line_2 != '') {
			$room_location[] = $result_data->rooms->rooms_address->address_line_2;
		}
		if ($result_data->rooms->rooms_address->city != '') {
			$room_location[] = $result_data->rooms->rooms_address->city;
		}
		if ($result_data->rooms->rooms_address->state != '') {
			$room_location[] = $result_data->rooms->rooms_address->state;
		}
		if ($result_data->rooms->rooms_address->country != '') {
			$room_location[] = $result_data->rooms->rooms_address->country_name;
		}


	$currency_details = Currency::where('code', $user->currency_code)->first()->toArray();
	$update_date = new DateTime(date('Y-m-d', $this->helper->custom_strtotime($result_data->updated_at)));
	
	$update_date = date('D, F d, Y');
	$coupon_amount = $result_data->coupon_code != 'Travel_Credit' ? $result_data->coupon_amount : '0';
	$travel_credit = $result_data->coupon_code == 'Travel_Credit' ? $result_data->coupon_amount : '0';
			


		$response_data = array(
				'reservation_id' => $result_data->id,
				'room_id' => $result_data->room_id,
				'list_type' => $result_data->list_type,
				'expire_timer' =>$expire_timer,
				'user_type'	=>$user_type,
				'trip_status' => $result_data->status,
				'booking_status' => @$booking_status != null ? $booking_status : '',
				'trip_date' => $result_data->dates,
				'guest_details' => array(
					'user_id' => $result_data->users->id,
					'user_name' => $result_data->users->full_name,
					'user_thumb_image' => @ProfilePicture::where('user_id',$result_data->user_id)->first()->header_src,
					'since_from' => $result_data->users->since,
				),
				'host_details' => array(
					'user_id' => $result_data->host_users->id,
					'user_name' => $result_data->host_users->full_name,
					'user_thumb_image' => @ProfilePicture::where('user_id',$result_data->host_id)->first()->header_src,
					'since_from' => $result_data->host_users->since,
				),
				'start_time' => date('H:i',strtotime($result_data->start_time)),
				'end_time' => date('H:i',strtotime($result_data->end_time)),		
				'check_in' => $result_data->checkin_date,
				'check_out' => $result_data->checkout_date,
				'room_name' => $result_data->rooms->name,
				'room_location' => implode(',', $room_location),
				'room_type' =>  $result_data->list_type!='Experiences' ? $result_data->rooms->room_type_name:$result_data->rooms->category_details->name,
				'total_nights' => $result_data->nights,
				'guest_count' => @$result_data->number_of_guests != null ? $result_data->number_of_guests : '0',
				'room_image' => $result_data->rooms->photo_name,
				'total_cost' => ($result_data->check_total >= 0) ? $result_data->check_total : 0,
				'host_fee' => $result_data->host_fee,
				'total_trips_count' => '1',
				'host_user_id' => $result_data->host_id,
				'per_night_price' => @(string) $result_data->base_per_night,
				'subtotal' => $result_data->base_per_night*$result_data->number_of_guests,
				'length_of_stay_type' => @(string) $result_data->length_of_stay_type,
				'length_of_stay_discount' => @$result_data->length_of_stay_discount,
				'length_of_stay_discount_price' => @$result_data->length_of_stay_discount_price,
				'booked_period_type' => @(string) $result_data->booked_period_type,
				'booked_period_discount' => @$result_data->booked_period_discount,
				'booked_period_discount_price' => @$result_data->booked_period_discount_price,
				'service_fee' => @(string) $result_data->service,
				'security_deposit' => @(string) $result_data->security,
				'cleaning_fee' => @(string) $result_data->cleaning,
				'additional_guest_fee' => @(string) $result_data->additional_guest,
				'payment_recieved_date' => $result_data->transaction_id !== ''? $update_date : '',
				'can_view_receipt' => $result_data->status == 'Accepted' ? 'Yes' : 'No',
				'coupon_amount' => (string) $coupon_amount,
				'travel_credit' => (string) $travel_credit,
				'host_penalty_amount' => @(string) $result_data->hostPayouts->total_penalty_amount,
				'currency_symbol' => $currency_details['original_symbol'],
				'trips_type' =>$reservation_status,
		);
		$sucess = array(
				'success_message' => trans('messages.api.trip_details_listed_success'),
				'status_code' => '1',
		);
		return json_encode(array_merge($sucess, ['instant_trip_details' => $response_data]));
	}


	/**
	 *Display trips details
	 *
	 * @param  Get method inputs
	 * @return Response in Json
	 */
	public function trips_details(Request $request) {

		$trips_array = array('pending_trips', 'current_trips', 'upcoming_trips', 'previous_trips');
		//check request trips type is valid or not
		if (!in_array($request->trips_type, $trips_array)) {
			return response()->json([
				'success_message' => trans('messages.api.invalid_trip_type'),
				'status_code' => '0',
			]);
		}

		$user = JWTAuth::parseToken()->authenticate();
		//get pre_accepted reservation details
		$preaccepted = Reservation::where('status', '=', 'Pre-Accepted')->where('date_check', '!=', 'No')->get();

		foreach ($preaccepted as $chck_pre) {
			$chck_pre->avablity;
		}

			//get pending trips details
		$data['pending_trips'] = Reservation::with(['users', 'rooms' => function ($query) {
			$query->with('rooms_address');
		}, 'host_users'])
			->where(function ($query) {
				$query->where('status', 'Pending')->orwhere('status', 'Pre-Accepted')->orwhere('status', 'Pre-Approved');
			})
			->where('type','!=','contact')
			->where('checkin', '>=', date('Y-m-d'))
			->where('user_id', $user->id)->orderBy('id', 'DESC')->get();

		//get currenct trips details
		$data['current_trips'] = Reservation::with(['users', 'rooms' => function ($query) {
			$query->with('rooms_address');
		}, 'host_users'])->where(function ($query) {
			$query->where(function ($query) {
				$query->where('checkin', '>=', date('Y-m-d'))->where('checkout', '<=', date('Y-m-d'));
			})->orWhere(function ($query) {
				$query->where('checkin', '<=', date('Y-m-d'))->where('checkout', '>=', date('Y-m-d'));
			});
		})->where('status', '!=', 'Pending')->where('status', '!=', 'Pre-Accepted')->where('type', '!=', 'contact')->where('user_id', $user->id)->where('type', '!=', 'contact')->orderBy('id', 'DESC')->get();
		//get upcoming trips details
		$data['upcoming_trips'] = Reservation::with(['users', 'rooms' => function ($query) {
			$query->with('rooms_address');
		}, 'host_users'])->where('checkin', '>', date('Y-m-d'))->where('type', '!=', 'contact')->where('status', '!=', 'Pre-Accepted')->where('status', '!=', '')->where('status', '!=', 'Pending')->where('type', '!=', 'contact')->where('user_id', $user->id)->orderBy('id', 'DESC')->get();
		//get previous trips details
		$data['previous_trips'] = Reservation::with(['users', 'rooms' => function ($query) {
			$query->with('rooms_address');
		}, 'host_users'])->where('checkout', '<', date('Y-m-d'))->where('type', '!=', 'contact')->where('user_id', $user->id)->orderBy('id', 'DESC')->get();

		$result = $data[$request->trips_type];
		//get currecy details
		$currency_details = Currency::where('code', $user->currency_code)->first()->toArray();

		foreach ($result as $result_data) {
			//check current trips is pending trips
			if ($request->trips_type == 'pending_trips') {

				$room_location[] = $result_data->rooms->rooms_address->country_name;

				$date = date('Y-m-d');
				//check pending checkin date greater then current date
				if ($result_data->checkin < $date) {
					if ($result_data->status == 'Pre-Accepted') {
						@$booking_status = 'Already Booked';
					}
				} else {


					if ($result_data->status == 'Pre-Accepted' || $result_data->status == 'Pre-Approved') {
						//get booking status available or not
						@$booking_status = $this->get_status(
							$result_data->room_id,
							$result_data->checkin,
							$result_data->checkout,
							$result_data->number_of_guests
						);

					} else {
						@$booking_status = '';
					}

				}
			}
			// check trips type
			if ($request->trips_type == 'current_trips' ||
				$request->trips_type == 'upcoming_trips' ||
				$request->trips_type == 'previous_trips') {
				$room_location = array();
				//get room address with remove null value
				if ($result_data->rooms->rooms_address->address_line_1 != '') {

					$room_location[] = $result_data->rooms->rooms_address->address_line_1;

				}
				if ($result_data->rooms->rooms_address->address_line_2 != '') {

					$room_location[] = $result_data->rooms->rooms_address->address_line_2;

				}
				if ($result_data->rooms->rooms_address->city != '') {

					$room_location[] = $result_data->rooms->rooms_address->city;

				}
				if ($result_data->rooms->rooms_address->state != '') {

					$room_location[] = $result_data->rooms->rooms_address->state;

				}
				if ($result_data->rooms->rooms_address->country != '') {

					$room_location[] = $result_data->rooms->rooms_address->country;

				}

			}

			$update_date = new DateTime(date('Y-m-d', $this->helper->custom_strtotime($result_data->updated_at)));

			$update_date = date('D, F d, Y');

			$coupon_amount = $result_data->coupon_code != 'Travel_Credit' ? $result_data->coupon_amount : '0';

			$travel_credit = $result_data->coupon_code == 'Travel_Credit' ? $result_data->coupon_amount : '0';

			$response_data[] = array(

				'reservation_id' => $result_data->id,

				'room_id' => $result_data->room_id,

				'list_type' => $result_data->list_type,

				'user_name' => $result_data->users->full_name,

				'user_thumb_image' => @ProfilePicture::where(

					'user_id', $result_data->user_id)

					->first()->header_src,

				'trip_status' => $result_data->status,

				'booking_status' => @$booking_status != null

				? $booking_status : '',

				'trip_date' => $result_data->dates,

				'guest_details' => $result_data->guest_details,

				'start_time' => date('H:i',strtotime($result_data->start_time)),
				
				'end_time' => date('H:i',strtotime($result_data->end_time)),
				
				'check_in' => $result_data->checkin_date,

				'check_out' => $result_data->checkout_date,

					'room_name' => $result_data->rooms->name,

					'room_location' => implode(',', $room_location),

					'room_type' =>  $result_data->list_type!='Experiences' ? $result_data->rooms->room_type_name:$result_data->rooms->category_details->name,

					'total_nights' => $result_data->nights,

					'guest_count' => @$result_data->number_of_guests != null

					? $result_data->number_of_guests : '0',

					'host_user_name' => $result_data->host_users->full_name,

					'host_thumb_image' => ProfilePicture::where(

						'user_id', $result_data->host_id)->first()

						->header_src,

					'room_image' => $result_data->rooms->photo_name,

					'total_cost' => $result_data->check_total,

					'total_trips_count' => count($data[$request->trips_type]),

					'host_user_id' => $result_data->host_id,

					'per_night_price' => @(string) $result_data->base_per_night,
					'subtotal' => $result_data->base_per_night*$result_data->number_of_guests,
					'length_of_stay_type' => @(string) $result_data->length_of_stay_type,
					'length_of_stay_discount' => @$result_data->length_of_stay_discount,
					'length_of_stay_discount_price' => @$result_data->length_of_stay_discount_price,
					'booked_period_type' => @(string) $result_data->booked_period_type,
					'booked_period_discount' => @$result_data->booked_period_discount,
					'booked_period_discount_price' => @$result_data->booked_period_discount_price,

					'service_fee' => @(string) $result_data->service,

					'security_deposit' => @(string) $result_data->security,

					'cleaning_fee' => @(string) $result_data->cleaning,

					'additional_guest_fee' => @(string) $result_data->additional_guest,

					'payment_recieved_date' => $result_data->transaction_id !== ''
					? $update_date : '',

					'can_view_receipt' => $result_data->status == 'Accepted'

					? 'Yes' : 'No',

					'coupon_amount' => (string) $coupon_amount,
					'travel_credit' => (string) $travel_credit,
					'host_penalty_amount' => @(string) $result_data->hostPayouts->total_penalty_amount,

					'currency_symbol' => $currency_details['original_symbol']);
			}
			if (@$response_data == null) {
				return response()->json([
					'success_message' => trans('messages.api.no_data_found'),
					'status_code' => '0',
				]);
			}

			$sucess = array(
				'success_message' => trans('messages.api.trip_details_listed_success'),
				'status_code' => '1',
			);

			return json_encode(
				array_merge($sucess, [$request->trips_type => $response_data]), JSON_UNESCAPED_SLASHES);
		}

		/**
	 * Get days between two dates
	 *
	 * @param date $sStartDate  Start Date
	 * @param date $sEndDate    End Date
	 * @return array $days      Between two dates
	 */
		public function get_days($sStartDate, $sEndDate) {
			$sStartDate = $this->payment_helper->date_convert($sStartDate);
			$sEndDate = $this->payment_helper->date_convert($sEndDate);
			$aDays[] = $sStartDate;
			$sCurrentDate = $sStartDate;

			while ($sCurrentDate < $sEndDate) {
				$sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));
			$aDays[] = $sCurrentDate;
		}

		return $aDays;
	}
	/**
	 * Check room already booked or not
	 *
	 * @param room_id
	 * @param start_date
	 * @param end_date
	 * @return array $days      Between two dates
	 */
	public function check_already_booked($room_id, $start_date, $end_date) {

		//check room staus already booked or not
		$data = $this->payment_helper->price_calculation($room_id, $start_date, $end_date, '', '', '');

		$data = json_decode($data, TRUE);

		$result = @$data['status'];

		if ((isset($data['status'])) && ($result == 'Not available')) {

			return 'Aready_booked';
		} else {

			return 'Available';

		}

	}
	/**
	 * Check room status
	 *
	 * @param  room_id
	 * @param  start_date
	 * @param  end_date
	 * @return status
	 */

	 	   

	public function get_status($room_id, $start_date, $end_date, $number_of_guests) {
		$room_id = $room_id;
		$checkin  = date('Y-m-d', $this->helper->custom_strtotime($start_date));
        $checkout = date('Y-m-d', $this->helper->custom_strtotime($end_date));
        $days     = $this->get_days_search($checkin, $checkout);
        unset($days[count($days)-1]);
        $count_reservation = Calendar::where('room_id',$room_id)->daysNotAvailable($days, $data['number_of_guests'])->get();
        if($count_reservation->count() == 0) {
        	return "Available"; 
        }else{
			return "Already Booked";
		}
	}

	public function get_days_search($sStartDate, $sEndDate){            
        $aDays[]      = $sStartDate;  
        $sCurrentDate = $sStartDate;  
        while($sCurrentDate < $sEndDate){
            $sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));  
            $aDays[]      = $sCurrentDate;  
        }
        return $aDays;  
    }



	/**
	 * Pending Reservation Cancel by Guest
	 *
	 * @param Get method inputs
	 * @return Response in Json
	 */
	public function guest_cancel_pending_reservation(Request $request) {

		$rules = array('reservation_id' => 'required|exists:reservation,id');

		$niceNames = array('reservation_id' => 'Reservation Id');

		$messages = array('required' => ':attribute is required.');

		$validator = Validator::make($request->all(), $rules, $messages);

		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {
			$error = $validator->messages()->toArray();

			foreach ($error as $er) {
				$error_msg[] = array($er);

			}

			return response()->json([

				'success_message' => $error_msg['0']['0']['0'],

				'status_code' => '0']);
		} else {

			$user = JWTAuth::parseToken()->authenticate();

			$reservation_details = Reservation::find($request->reservation_id);
			//check valid user or not
			if ($user->id != $reservation_details->user_id) {

				return response()->json([

					'success_message' => 'Permission Denied ',

					'status_code' => '0']);

			}

			if ($reservation_details->status == 'Cancelled' ||

				$reservation_details->status == 'Declined' ||

				$reservation_details->status == 'Expired') {
				return response()->json([

					'success_message' => 'Not Available',

					'status_code' => '0']);
			}

			$messages = new Messages;

			$messages->room_id = $reservation_details->room_id;

			$messages->reservation_id = $reservation_details->id;

			$messages->user_to = $reservation_details->host_id;

			$messages->user_from = $user->id;

			$messages->message = $this->helper->phone_email_remove($request->cancel_message);
			$messages->message_type = 10;

			$messages->save();

			//cancel reservation by guest
			$cancel = Reservation::find($request->reservation_id);

			$cancel->cancelled_by = "Guest";

			$cancel->cancelled_reason = $request->cancel_reason;

			$cancel->cancelled_at = date('Y-m-d H:m:s');

			$cancel->status = "Cancelled";

			$cancel->updated_at = date('Y-m-d H:m:s');

			$cancel->save();

			$email_controller = new EmailController;
			$email_controller->cancel_guest($cancel->id);

			return response()->json([

				'success_message' => 'Reservation Successfully Cancelled',

				'status_code' => '1']);
		}
	}
	/**
	 * Reservation Cancel by Guest
	 *
	 * @param Get method inputs
	 * @return Response in Json
	 */
	public function guest_cancel_reservation(Request $request) {


		$rules = array('reservation_id' => 'required|exists:reservation,id');
		$niceNames = array('reservation_id' => 'Reservation Id');
		$messages = array('required' => ':attribute is required.');
		$validator = Validator::make($request->all(), $rules, $messages);
		$validator->setAttributeNames($niceNames);

		if ($validator->fails()) {
			
			$error = $validator->messages()->toArray();
			foreach ($error as $er) {
				$error_msg[] = array($er);
			}

			return response()->json([
				'success_message' => $error_msg['0']['0']['0'],
				'status_code' => '0',

			]);

		} else {

			$user = JWTAuth::parseToken()->authenticate();
			$reservation_details = Reservation::find($request->reservation_id);
			//check valid user or not
			if ($user->id != $reservation_details->user_id) {
				return response()->json([
					'success_message' => 'Permission Denied',
					'status_code' => '0']);

			}
			//Prevent Cancel Reservation
			if ($reservation_details->status == 'Cancelled') {
				return response()->json([
					'success_message' => 'Not Available',
					'status_code' => '0']);
			}

			$rooms_details = Rooms::find($reservation_details->room_id);
			if ($reservation_details->status != "Accepted") {
				return response()->json([
					'success_message' => 'Not Available',

					'status_code' => '0',

				]);
			}

			if($reservation_details->list_type == 'Experiences')
	        {

	            $this->guest_cancel_experience_reservation($reservation_details);

	            $reservation_details->cancelled_by = "Guest";
	            $reservation_details->cancelled_reason = $request->cancel_reason;
	            $reservation_details->cancelled_at = date('Y-m-d H:m:s');
	            $reservation_details->status = "Cancelled";
	            $reservation_details->updated_at = date('Y-m-d H:m:s');
	            $reservation_details->save();

	            $messages = new Messages;
	            $messages->room_id        = $reservation_details->room_id;
	            $messages->list_type      = 'Experiences';
	            $messages->reservation_id = $reservation_details->id;
	            $messages->user_to        = $reservation_details->host_id;
	            $messages->user_from      = Auth::user()->id;
	            $messages->message        = $this->helper->phone_email_remove($request->cancel_message);
	            $messages->message_type   = 10;
	            $messages->save();

	            $email_controller = new EmailController;
	            $email_controller->experience_booking_cancelled($reservation_details->id);

	            return response()->json([

					'success_message' => 'Reservation Successfully Cancelled',

					'status_code' => '1',

				]);

	            
	        }

			$host_fee_percentage = Fees::find(2)->value > 0 ? Fees::find(2)->value : 0;
			$host_payout_amount = $reservation_details->subtotal;
			$guest_refundable_amount = 0;

			$datetime1 = new DateTime(date('Y-m-d'));
			$datetime2 = new DateTime(date('Y-m-d', strtotime($reservation_details->checkin)));
			$interval_diff = $datetime1->diff($datetime2);
			$interval = $interval_diff->days;

			$per_night_price = $reservation_details->per_night;
			$total_nights = $reservation_details->nights;

			// Additional guest price is added to the per night price for calculation
			$additional_guest_per_night = ($reservation_details->additional_guest / $total_nights);
			$per_night_price = $per_night_price + $additional_guest_per_night;

			$total_night_price = $per_night_price * $total_nights;
			if ($interval_diff->invert) // To check the check in is less than today date
			{
				$spend_night_price = $per_night_price * ($interval <= $total_nights ? $interval : $total_nights);
				$remain_night_price = $per_night_price * (($total_nights - $interval) > 0 ? ($total_nights - $interval) : 0);
			} else {
				$spend_night_price = 0;
				$remain_night_price = $total_night_price;
			}

			$additional_guest_price = /*$reservation_details->additional_guest*/0;
			$cleaning_fees = $reservation_details->cleaning;
			$security_deposit = /*$reservation_details->security*/0;
			$coupon_amount = $reservation_details->coupon_amount;
			$service_fee = $reservation_details->service;
			$host_payout_ratio = (1 - ($host_fee_percentage / 100));

			if ($reservation_details->cancellation == "Flexible") {
				if ($interval_diff->invert) // To check the check in is less than today date
				{
					if ($interval > 0) //  (interval < 0) condition
					{
						$refund_night_price = $remain_night_price;
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = $spend_night_price;
						$host_payout_amount = array_sum([
							$payout_night_price,
							$additional_guest_price,
							$cleaning_fees,
						]);
					}
				} else {
					if ($interval == 0) //  (interval = 0) condition
					{
						$refund_night_price = $total_night_price - $per_night_price;
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$additional_guest_price,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = $per_night_price;
						$host_payout_amount = array_sum([
							$payout_night_price,
							$cleaning_fees,
						]);
					} else if ($interval > 0) //  (interval > 0) condition
					{
						$refund_night_price = $total_night_price;
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$additional_guest_price,
							$cleaning_fees,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = 0;
						$host_payout_amount = array_sum([
							$payout_night_price,
						]);
					}
				}
			} else if ($reservation_details->cancellation == "Moderate") {
				if ($interval_diff->invert) // To check the check in is less than today date
				{
					if ($interval > 0) //  (interval < 0) condition
					{
						$refund_night_price = $remain_night_price * (50 / 100); // 50 % of remain night price
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = $spend_night_price + ($remain_night_price * (50 / 100)); // spend night price and 50% remain night price
						$host_payout_amount = array_sum([
							$payout_night_price,
							$additional_guest_price,
							$cleaning_fees,
						]);
					}
				} else {
					if ($interval < 5 && $interval >= 0) //  (interval < 5 && interval >= 0) condition
					{
						$refund_night_price = ($total_night_price - $per_night_price) * (50 / 100); // 50% of other than first night price
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$additional_guest_price,
							$cleaning_fees,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = $per_night_price + (($total_night_price - $per_night_price) * (50 / 100)); // First night price and 50% other night price
						$host_payout_amount = array_sum([
							$payout_night_price,
						]);
					} else if ($interval >= 5) //  (interval >= 5) condition
					{
						$refund_night_price = $total_night_price;
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$additional_guest_price,
							$cleaning_fees,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = 0;
						$host_payout_amount = array_sum([
							$payout_night_price,
						]);
					}
				}
			} else if ($reservation_details->cancellation == "Strict") {
				if ($interval_diff->invert) // To check the check in is less than today date
				{
					if ($interval > 0) //  (interval < 0) condition
					{
						$refund_night_price = 0; // Total night price is non refundable
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = $total_night_price; // Total night price is payout
						$host_payout_amount = array_sum([
							$payout_night_price,
							$additional_guest_price,
							$cleaning_fees,
						]);
					}
				} else {
					if ($interval < 7 && $interval >= 0) //  (interval < 7 && interval >= 0) condition
					{
						$refund_night_price = 0; // Total night price is non refundable
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$additional_guest_price,
							$cleaning_fees,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = $total_night_price; // Total night price is payout
						$host_payout_amount = array_sum([
							$payout_night_price,
						]);
					} else if ($interval >= 7) //  (interval >= 7) condition
					{
						$refund_night_price = $total_night_price * (50 / 100); // 50% of total night price;
						$guest_refundable_amount = array_sum([
							$refund_night_price,
							$additional_guest_price,
							$cleaning_fees,
							$security_deposit,
							-$coupon_amount,
						]);

						$payout_night_price = $total_night_price * (50 / 100); // 50% of total night price;
						$host_payout_amount = array_sum([
							$payout_night_price,
						]);
					}
				}
			}

			$host_fee = ($host_payout_amount * ($host_fee_percentage / 100));
			$host_payout_amount = $host_payout_amount * $host_payout_ratio;

			$this->payment_helper->payout_refund_processing($reservation_details, $guest_refundable_amount, $host_payout_amount);

			// Update Calendar, delete stayed date
			// $cancelled_date = date('Y-m-d H:m:s');
			$days = $this->get_days($reservation_details->checkin, $reservation_details->checkout);
			for ($j = 0; $j < count($days) - 1; $j++) {
				$calendar_detail = Calendar::where('room_id', $reservation_details->room_id)->where('date', $days[$j]);
				if ($calendar_detail->get()->count()) {
					// $calendar_price=$calendar_detail->get()->first()->price;
					$calendar_row = $calendar_detail->first();
					$calendar_price = $calendar_row->price;
					$calendar_row->spots_booked = $calendar_row->spots_booked - $reservation_details->number_of_guests;
					$calendar_row->save();
					if ($calendar_row->spots_booked <= 0) {
						if ($calendar_price != "0") {
							$calendar_row->status = 'Available';
							$calendar_row->save();
						} else {
							$calendar_row->delete();
						}
					}
				}
			}

			// Send message for cancellation
			$messages = new Messages;
			$messages->room_id = $reservation_details->room_id;
			$messages->reservation_id = $reservation_details->id;
			$messages->user_to = $reservation_details->host_id;
			$messages->user_from = $user->id;
			$messages->message = $this->helper->phone_email_remove($request->cancel_message);
			$messages->message_type = 10;
			$messages->save();

			// Update reservation status and other details
			$cancel = Reservation::find($reservation_details->id);
			$cancel->host_fee = $host_fee;
			$cancel->cancelled_by = "Guest";
			$cancel->cancelled_reason = $request->cancel_reason;
			$cancel->cancelled_at = date('Y-m-d H:m:s');
			$cancel->status = "Cancelled";
			$cancel->updated_at = date('Y-m-d H:m:s');
			$cancel->save();

			// Send mail to host
			$email_controller = new EmailController;
			$email_controller->cancel_guest($cancel->id);

			return response()->json([

				'success_message' => 'Reservation Successfully Cancelled',

				'status_code' => '1',

			]);

		}
	}

	/**
     * Host Experience Reservation cancel by Guest
     *
     * @param App\Models\Reservation $reservation_details
     */
    public function guest_cancel_experience_reservation($reservation_details)
    {
        $today_date_time = new DateTime(); 
        $start_date_time = new DateTime(date('Y-m-d', $this->helper->custom_strtotime($reservation_details->checkin))); 
        $created_date_time = new DateTime(date('Y-m-d', $this->helper->custom_strtotime($reservation_details->created_at))); 
        $interval_to_start = $today_date_time->diff($start_date_time);
        $interval_from_created = $created_date_time->diff($today_date_time);
        $interval_start = $interval_to_start->days;
        $interval_created = $interval_from_created->days;
        
        $host_payout_amount = $reservation_details->subtotal;
        $guest_refundable_amount = 0;
        $pending_guest_refund = 'No';

        $guest_details = $reservation_details->guest_details;
        $spots = $guest_details->pluck('spot')->toArray();

        if($today_date_time < $start_date_time)
        {
            if($interval_created <= 1)
            {
                $guest_refundable_amount = $reservation_details->total-$reservation_details->coupon_amount;
                $host_payout_amount = 0;
            }
            else if($interval_start >= 30)
            {
                $guest_refundable_amount = $reservation_details->total-$reservation_details->coupon_amount;
                $host_payout_amount = 0;
            }
            else if($interval_start < 30){
                $guest_refundable_amount = 0;
                $pending_guest_refund = 'Yes';
            }
        }
        else
        {
            $guest_refundable_amount = 0;
        }
        HostExperiencePaymentController::payout_refund_processing($reservation_details, $guest_refundable_amount, $host_payout_amount, $spots);
            
        if($pending_guest_refund == 'Yes')
        {
            foreach($guest_details as $guest)
            {
                $guest->refund_status = 'Pending';
                $guest->save();
            }
        }

        $calendar = HostExperienceCalendar::where('host_experience_id', $reservation_details->room_id)->where('date', $reservation_details->checkin)->first();
        if($calendar)
        {
            $calendar_spots = $calendar->spots_array;
         
            $updated_calendar_spots = array_diff($calendar_spots, $spots);
            $updated_calendar_spots = array_filter($updated_calendar_spots);
            asort($updated_calendar_spots);

            $calendar->spots = implode(',', $updated_calendar_spots);
            $calendar->spots_booked = count($updated_calendar_spots);
            $calendar->save();

            if($calendar->spots_booked == 0)
            {
                $calendar->delete();
            }
        }
    }

}
