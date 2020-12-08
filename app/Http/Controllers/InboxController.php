<?php

/**
 * Inbox Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Inbox
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailController;
use App\Models\Messages;
use App\Models\Reservation;
use App\Models\Rooms;
use App\Models\Fees;
use App\Models\SpecialOffer;
use App\Models\Calendar;
use App\Models\Currency;
use App\Models\HostExperiences;
use Illuminate\Support\Facades\Redirect;
use DB;
use Validator;
use App\Http\Helper\PaymentHelper;
use App\Http\Start\Helpers;
use DateTime;
use Session;
use LRedis;
use App\Models\User;


class InboxController extends Controller
{
    protected $helper; // Global variable for Helpers instance

    /**
     * Constructor to Set Helpers instance in Global variable
     *
     * @param array $helper   Instance of Helpers
     */
     protected $payment_helper; // Global variable for Helpers instance
    
    public function __construct(PaymentHelper $payment)
    {
        $this->payment_helper = $payment;
        $this->helper = new Helpers;
    }

    /**
     * Load Inbox View
     *
     * @return Inbox page view file
     */
    public function index()
    {
        $data['user_id']     = Auth::id();
        $data['all_message'] = Messages::all_messages($data['user_id']);
        //dd($data);
        return view('users.inbox', $data);
    }
    
    /**
     * Ajax function for update Archive messages
     *
     * @param array $request  Input values
     */
    public function archive(Request $request)
    {
        $id     = $request->id;
        $msg_id = $request->msg_id;
        $type   = trim($request->type);

        if($type == "Archive")
            Messages::where('user_from', $id)->where('id', $msg_id )->update(['archive' =>'1']);
        
        if($type == "Unarchive")
            Messages::where('user_from', $id)->where('id', $msg_id )->update(['archive' =>'0']);

        // update read to 1 and archive to 1 for all messages related to reservation
        $message = Messages::find($msg_id);
        Messages::where('reservation_id',$message->reservation_id)->where('user_to',Auth::id())->update(['archive' => $type == 'Archive' ? '1' : '0']);
    }
    
    /**
     * Ajax function for update Star messages
     *
     * @param array $request  Input values
     */
    public function star(Request $request)
    {
        $id     = $request->id;
        $msg_id = $request->msg_id;
        $type   = trim($request->type);

        if($type == "Star")
            Messages::where('user_from', $id)->where('id', $msg_id )->update(['star' =>'1']);
        
        if($type == "Unstar")
            Messages::where('user_from', $id)->where('id', $msg_id )->update(['star' =>'0']);

        // update read to 1 and archive to 1 for all messages related to reservation
        $message = Messages::find($msg_id);
    }

    /**
     * Ajax function for Message counts
     *
     * @param array $request  Input values
     * @return json message counts
     */
    public function message_count(Request $request)
    {
        $data['user_id'] = $user_id = Auth::id();
        
        $all_message = Messages::all_messages($data['user_id']);

        $all    =   Messages::whereIn('id', function($query) use($user_id) {   
                        $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                    })
                    ->where('archive','0')
                    ->count();

        $star   =   Messages::whereIn('id', function($query) use($user_id) {   
                        $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                    })
                    ->where('archive','0')
                    ->where('star', '1')
                    ->count();

        $unread  =  Messages::whereIn('id', function($query) use($user_id) {   
                        $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                    })
                    ->where('archive','0')
                    ->where('read','0')
                    ->count();

        $reserve =  Messages::whereIn('id', function($query) use($user_id) {
                        $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->where('reservation_id','!=', 0)->groupby('reservation_id');
                    })
                    ->with(['reservation'])
                    ->whereHas('reservation' ,function($query) {
                        $query->where('status','!=','')->where('status','!=','Pending');
                    })
                    ->where('archive','0')
                    ->count();

        $pending =  Messages::whereIn('id', function($query) use($user_id) {
                        $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                    })
                    ->with(['reservation'])
                    ->whereHas('reservation' ,function($query) {
                        $query->where('status','Pending');
                    })
                    ->where('archive','0')
                    ->count();

        $archive =  Messages::whereIn('id', function($query) use($user_id) {
                        $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                    })
                    ->where('archive', '1')
                    ->count();

        $admin_messages = Messages::whereIn('id', function($query) use($user_id) {   
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->where('user_to',$user_id)
                        ->where('user_from',$user_id)
                        ->where('archive','0')
                        ->orderBy('id','desc')
                        ->count();

        if($all_message->count() != 0 ) {
            $data['all_message_count'] = $all;
            $data['stared_count']      = $star;
            $data['unread_count']      = $unread;
            $data['reservation_count'] = $reserve;
            $data['archived_count']    = $archive;
            $data['pending_count']     = $pending;
            $data['admin_messages']    = $admin_messages;
        }
        else {
            $data['all_message_count'] = 0;
            $data['stared_count']      = 0;
            $data['unread_count']      = 0;
            $data['reservation_count'] = 0;
            $data['archived_count']    = 0;
            $data['pending_count']     = 0;
            $data['admin_messages']    = 0;
        }

        return json_encode($data);
    }

    /**
     * Ajax function for Message Filters
     *
     * @param array $request  Input values
     * @return json message counts
     */
    public function message_by_type(Request $request)
    {
        $user_id = Auth::id();
        $type    = trim($request->type);

        if($type == "starred") {
            $result =   Messages::whereIn('id', function($query) use($user_id) {   
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->with(['user_details' => function($query) {
                            $query->with('profile_picture');
                        }])
                        ->with(['reservation' => function($query) {
                            $query->with('currency');
                        }])
                        ->with(['rooms' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->with(['host_experience' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->where('archive','0')
                        ->where('star', '1')
                        ->orderBy('id','desc');
        }
        else if($type == "hidden") {
            $result =   Messages::whereIn('id', function($query) use($user_id) {   
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->with(['user_details' => function($query) {
                            $query->with('profile_picture');
                        }])
                        ->with(['reservation' => function($query) {
                            $query->with('currency');
                        }])
                        ->with(['rooms' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->with(['host_experience' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->where('archive', '1')
                        ->orderBy('id','desc');
        }
        else if($type == "unread") {
            $result =   Messages::whereIn('id', function($query) use($user_id) {   
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->with(['user_details' => function($query) {
                            $query->with('profile_picture');
                        }])
                        ->with(['reservation' => function($query) {
                            $query->with('currency');
                        }])
                        ->with(['rooms' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->with(['host_experience' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->where('read','0')
                        ->where('archive','0')
                        ->orderBy('id','desc');
        }
        else if($type == "reservations") {
            $result =   Messages::whereIn('id', function($query) use($user_id) {   
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->with(['user_details' => function($query) {
                            $query->with('profile_picture');
                        }])
                        ->with(['reservation' => function($query) {
                            $query->with('currency');
                        }])
                        ->whereHas('reservation' ,function($query) {
                            $query->where('status','!=','')->where('status','!=','Pending');
                        })
                        ->with(['rooms' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->with(['host_experience' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->where('reservation_id','!=', '0')
                        ->where('archive','0')
                        ->orderBy('id','desc');
        }
        else if($type == "admin_messages") {
            $result = Messages::whereIn('id', function($query) use($user_id) {   
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->where('user_to',$user_id)
                        ->where('user_from',$user_id)
                        ->where('archive','0')
                        ->orderBy('id','desc');
            
        }
        else if($type == "pending_requests") {
            $result =   Messages::whereIn('id', function($query) use($user_id) {
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->with(['user_details' => function($query) {
                            $query->with('profile_picture');
                        },'reservation'=>function($query){
                            $query->with('currency');
                        }])
                        ->whereHas('reservation' ,function($query) {
                            $query->where('status','Pending');
                        })
                        ->with(['rooms' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->where('archive','0')
                        ->orderBy('id','desc');
        }
        else {
            // All Messages
            $result =   Messages::whereIn('id', function($query) use($user_id) {   
                            $query->select(DB::raw('max(id)'))->from('messages')->where('user_to', $user_id)->groupby('reservation_id');
                        })
                        ->with(['user_details' => function($query) {
                            $query->with('profile_picture');
                        }])
                        ->with(['reservation' => function($query) {
                            $query->with('currency');
                        }])
                        ->with(['rooms' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->with(['host_experience' => function($query) {
                            $query->with('rooms_address');
                        }])
                        ->where('archive','0')
                        ->orderBy('id','desc');
        }
        
        $result =  $result->paginate(10)->toJson();

        return $result;
    }

    /**
     * Load Guest Conversation Page with Messages
     *
     * @param array $request  Input values
     * @return view Guest Conversation View File
     */
    public function guest_conversation(Request $request)
    {

        $reservation = Reservation::where('id', $request->id)->userRelated()->first();
        if(!$reservation)
            abort(404);
        
        $read_count   = Messages::where('reservation_id',$request->id)->where('user_to',Auth::id())->where('read','0')->count();
        
        if($read_count !=0)
            Messages::where('reservation_id',$request->id)->where('user_to',Auth::id())->update(['read' =>'1']);

        $data['messages'] = $messages = Messages::with('user_details','reservation')->where('reservation_id',$request->id)->orderBy('created_at','desc')->get();

        $instant_message = $this->payment_helper->InstantMessage($messages);
        //dd($instant_message->toArray());
        $data['instant_message'] = $instant_message->toJson();
        $data['user_id'] = Auth::id();
        $data['profile_picture'] = Auth::user()->profile_picture->src;
        $data['today'] = date('Y-m-d');

        $data['special_offer'] = SpecialOffer::where('id',@$data['messages'][0]['special_offer_id'])->get();


        if(@$data['messages'][0]->reservation->rooms->user_id == Auth::id())
            abort('404');
        // check avablity in special offer
        $data['avablity']=0;
        if(@$data['messages'][0]->special_offer_id!='')
        {
            $checkin_guest=$data['messages'][0]->special_offer->checkin;
            $checkout_guest=$data['messages'][0]->special_offer->checkout;
            $data['price_list'] = json_decode($this->payment_helper->price_calculation($data['messages'][0]->special_offer->room_id, $data['messages'][0]->special_offer->checkin,$data['messages'][0]->special_offer->checkout, $data['messages'][0]->special_offer->number_of_guests, $data['messages'][0]->special_offer_id,'',''));
            $data['checkin']=date(PHP_DATE_FORMAT,strtotime($checkin_guest));
            $data['checkout']=date(PHP_DATE_FORMAT,strtotime($checkout_guest));
            $from                       = new DateTime(date('Y-m-d', $this->helper->custom_strtotime($data['messages'][0]->special_offer->checkin)));
            $to                         = new DateTime(date('Y-m-d', $this->helper->custom_strtotime($data['messages'][0]->special_offer->checkout)));
            $data['special_offer_nights'] =  $to->diff($from)->format("%a");
        }
       
        //dd($data);
        $data["guest_percentage"]            = Fees::find(1)->value;
        $data['title'] = 'Conversation ';
        return view('users.guest_conversation', $data);
    }



    public function update_inbox_count(Request $request){


        $reservation_id = $request->reservation_id;
        $user_id = $request->user_id;
        $read_count   = Messages::where('reservation_id',$reservation_id)->where('user_to',$user_id)->where('read','0')->count();
        if($read_count !=0) {
            Messages::where('reservation_id',$reservation_id)->where('user_to',$user_id)->update(['read' =>'1']);  
        }
        $result['count'] = $this->payment_helper->InstantMessageCount($user_id);
        $redis = \LRedis::connection();
        $redis->publish('chat', json_encode($result));

    }
    /**
     * Ajax function for Conversation reply
     *
     * @param array $request  Input values
     * @return html Reply message html
     */
    public function reply(Request $request, EmailController $email_controller)
    {
        $reservation_details = Reservation::find($request->id);
        if($request->template == 'NOT_AVAILABLE' || $request->template == '9') {
            $reservation_details->decline_reason = $request->template == '9' ? $request->template_message : $request->template;

            $reservation_details->status = 'DECLINED';
            $reservation_details->save();
        }


        $message = $this->helper->phone_email_remove($request->message);

        //Guest Message
        if($reservation_details->user_id == Auth::id()) {

            $messages = new Messages;
            $messages->room_id        = $reservation_details->room_id;
            $messages->reservation_id = $reservation_details->id;
            $messages->user_to        = $reservation_details->rooms->user_id;
            $messages->user_from      = Auth::id();
            $messages->list_type      = $reservation_details->list_type;
            $messages->message        = $message;
            $messages->message_type   = 5;
            $messages->saveQuietly();

            $guest_message = Messages::with('reservation')->where('id',$messages->id)->get();
            $instant_message = $guest_message->map(function($messages){
                $message = $this->payment_helper->message_data($messages);
                $message['reservation'] = $this->payment_helper->reservation_data($messages->reservation,$messages->user_to);
                $message['special_offer'] = $this->payment_helper->special_data($messages->special_offer);
                return $message;
            });
            $instant_message_mobile = $guest_message->map(function($messages){
                    $sender_profile =  $messages->user_details->profile_picture->only('src');
                    $message['message'] = $messages->message;
                    $message['user_from'] = $messages->user_from;
                    $message['time']    = date('H:i a');
                    $message['sender_profile'] = $sender_profile['src'];
                    return $message;
            });

            $result['count'] = $this->payment_helper->InstantMessageCount($reservation_details->rooms->user_id);
            $result['instant_message'] = $instant_message[0];
            $result['instant_message_mobile'] = $instant_message_mobile[0];
            $result['reservation_id']  = $reservation_details->id; 
            $result['type']  = 'add';
            $result['inbox'] ='yes';
            $redis = \LRedis::connection();
            $redis->publish('chat', json_encode($result));

            $userDetails = User::find($reservation_details->rooms->user_id);
            logger($userDetails);
            $user_data =array(
                'device_id'  => $userDetails->device_id,
                'device_type' => $userDetails->device_type,
            );
            $notification_data = array(
                'key'            => 'Chat',
                'type'           => 'Host',
                'message'        => $message,        
                'title'          => 'New Message From '.ucfirst(Auth::user()->first_name),
                'reservation_id' => (string) $reservation_details->id,
                'host_user_id'     => (string)Auth::id(),
            );
            $this->payment_helper->SendPushNotification($user_data,$notification_data);
        }

        //Host Message with Special Offer or Pre Approve
        else if($reservation_details->rooms->user_id == Auth::id())
        {
            if($request->template == 1)
            {
                $message_type = 6;
                $special_offer = new SpecialOffer;
                $special_offer->reservation_id   = $reservation_details->id;
                $special_offer->room_id          = $reservation_details->room_id;
                $special_offer->user_id          = $reservation_details->user_id;
                $special_offer->checkin          = $reservation_details->checkin;
                $special_offer->checkout         = $reservation_details->checkout;
                $special_offer->number_of_guests = $reservation_details->number_of_guests;
                $special_offer->price            = $reservation_details->subtotal;
                $special_offer->currency_code    = Currency::first()->session_code;
                $special_offer->type             = 'pre-approval';
                $special_offer->created_at       = date('Y-m-d H:i:s');
                $special_offer->save();
       
                //pre approval status change
                if($reservation_details->type == 'contact') {
                    $reservation =Reservation::find($reservation_details->id);
                    $reservation->status             = 'pre-approved';
                    $reservation->created_at       = date('Y-m-d H:i:s');
                    $reservation->save();
                }

                $special_offer_id = $special_offer->id;
                $email_controller->preapproval($reservation_details->id, $message);
            }
            else if($request->template == 2) {
                
                $message_type = 7;
                $special_offer = new SpecialOffer;
                $rules = array(
                    'pricing_price' => 'required|numeric'
                );

                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput(); // Form calling with Errors and Input values
                }
                $cur_ency=$request->currency; 
                $minimum_amount = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, Session::get('currency'), MINIMUM_AMOUNT); 
                $currency_symbol = Currency::whereCode(Session::get('currency'))->first()->original_symbol;
                $night_price=$request->pricing_price;

                if($night_price < $minimum_amount && $night_price != ''){
                  return json_encode(['success'=>'false','msg' => trans('validation.min.numeric', ['attribute' => 'price', 'min' => $currency_symbol.$minimum_amount]), 'attribute' => 'price']);
                }

                $special_offer->reservation_id   = $reservation_details->id;
                $special_offer->room_id          = $request->pricing_room_id;
                $special_offer->user_id          = $reservation_details->user_id;
                $special_offer->checkin          = $this->payment_helper->date_convert($request->pricing_checkin);
                $special_offer->checkout         = $this->payment_helper->date_convert($request->pricing_checkout);
                $special_offer->number_of_guests = $request->pricing_guests;
                $special_offer->price            = $request->pricing_price;
                $special_offer->currency_code    = Currency::first()->session_code;
                $special_offer->type             = 'special_offer';
                $special_offer->created_at       = date('Y-m-d H:i:s');
                $special_offer->save();
                $special_offer_id = $special_offer->id;
                $email_controller->preapproval($reservation_details->id, $message, 'special_offer');
            }
            else if($request->template == 'NOT_AVAILABLE') {
                $message_type = 8;

                $blocked_days = $this->get_days($reservation_details->checkin, $reservation_details->checkout);
                
                // Update Calendar
                for($j=0; $j<count($blocked_days)-1; $j++)
                {
                    $calendar_data = [
                            'room_id' => $reservation_details->room_id,
                            'date'    => $blocked_days[$j],
                            'status'  => 'Not available',
                            'source'  => 'Calendar',
                            ];

                    Calendar::updateOrCreate(['room_id' => $reservation_details->room_id, 'date' => $blocked_days[$j]], $calendar_data);
                }
            }
            else if($request->template == 9)
                $message_type = 8;
            else
                $message_type = 5;
            
            $messages = new Messages;
            $messages->room_id          = $reservation_details->room_id;
            $messages->reservation_id   = $reservation_details->id;
            $messages->user_to          = $reservation_details->user_id;
            $messages->list_type        = $reservation_details->list_type;
            $messages->user_from        = Auth::id();
            $messages->message          = $message;
            $messages->message_type     = $message_type;
            $messages->special_offer_id = @$special_offer_id;
            $messages->saveQuietly();

            $guest_message = Messages::with('reservation')->where('id',$messages->id)->limit(1)->get();
            $instant_message = $guest_message->map(function($messages){
                $message = $this->payment_helper->message_data($messages);
                $message['reservation'] = $this->payment_helper->reservation_data($messages->reservation,$messages->user_to);
                $message['special_offer'] = $this->payment_helper->special_data($messages->special_offer);
                return $message;
            });

            $instant_message_mobile = $guest_message->map(function($messages){
                    $sender_profile =  $messages->user_details->profile_picture->only('src');
                    $message['message'] = $messages->message;
                    $message['user_from'] = $messages->user_from;
                    $message['time']    = date('H:i a');
                    $message['sender_profile'] = $sender_profile['src'];
                    return $message;
            });

            $result['count'] = $this->payment_helper->InstantMessageCount($reservation_details->user_id);
            
            $result['instant_message'] = $instant_message[0];
            $result['instant_message_mobile'] = $instant_message_mobile[0];
            $result['reservation_id']  = $reservation_details->id; 
            $result['type']  ='add';
            $result['inbox'] ='yes';
            $redis = \LRedis::connection();
            $redis->publish('chat', json_encode($result));

            $userDetails = User::find($reservation_details->user_id);
            $user_data =array(
                'device_id'  => $userDetails->device_id,
                'device_type' => $userDetails->device_type,
            );
            $notification_data = array(
                'key'            => 'Chat',
                'type'           => 'Guest',
                'message'        => $message,        
                'title'          => 'New Message From '.ucfirst(Auth::user()->first_name),
                'reservation_id' => (string) $reservation_details->id,
                'host_user_id'     => (string) Auth::id(),
            );

            $this->payment_helper->SendPushNotification($user_data,$notification_data);

        }
    }
    
    /**
     * Load Host Conversation Page with Messages
     *
     * @param array $request  Input values
     * @return view Host Conversation View File
     */
    public function host_conversation(Request $request, CalendarController $calendar)
    {

        $reservation = Reservation::where('id',$request->id)->userRelated()->firstOrFail();
        $read_count   = Messages::where('reservation_id',$request->id)->where('user_to',Auth::id())->where('read','0')->count();
        if($read_count != 0) {
            Messages::where('reservation_id',$request->id)->where('user_to',Auth::id())->update(['read' =>'1']);
        }
        
        $data['messages'] = $messages =  Messages::with('user_details','reservation','special_offer')->where('reservation_id',$request->id)->orderBy('created_at','desc')->get(); 

       $instant_message = $this->payment_helper->InstantMessage($messages);
       //dd($instant_message->toArray(),Auth::id());
       $data['instant_message'] = $instant_message->toJson();
       $data['user_id'] = Auth::id();

        $data['rooms'] = Rooms::where('user_id',auth()->id())->listed()->verified()->get()->pluck('name','id');
        if($data['messages'][0]->reservation->user_id == Auth::id()) {
            abort('404');
        }
        $data['edit_calendar_link'] = url('/').'/manage-listing/'.$data['messages'][0]->room_id.'/calendar';
        if($data['messages'][0]->reservation->list_type == "Experiences"){   
            $host_experiences_controller = new HostExperiencesController;
            $data['edit_calendar_link']  = url('/').'/host/manage_experience/'.$data['messages'][0]->room_id.'?step_num=1';
            $host_experiences = HostExperiences::authUser()->listed()->pluck('title', 'id');
            $data['rooms'] = $host_experiences;
            $data['calendar'] = $host_experiences_controller->get_calendar_view($data['messages'][0]->room_id, '', '', 'small');
        }else{
            $data['calendar'] = $calendar->generate_small($data['messages'][0]->reservation->room_id, '', '', $data['messages'][0]->reservation_id);
        }

        $data['status'] =  $reservation->status;       
       // dd($data['messages']);
        return view('users.host_conversation', $data);
    }




    public function admin_messages(Request $request)
    {
        
        if($request->id != Auth::id())
        abort(404);

        $read_count   = Messages::where('reservation_id',Null)->where('user_to',Auth::id())->where('read','0')->count();

        if($read_count !=0)
            Messages::where('reservation_id',Null)->where('user_to',Auth::id())->update(['read' =>'1']);


        $data['messages'] = Messages::where('reservation_id',Null)->where('user_to',$request->id)->where('user_from',$request->id)->orderBy('created_at','desc')->get();   
        
       $data['title'] = 'Admin Messages';
       

        return view('users.admin_conversation', $data);
    }

    /**
     * Load Small Calendar for Host Conversation Page
     *
     * @param array $request  Input values
     * @param array $calendar  Calendar Controller Instance
     * @return html Small Calendar
     */
    public function calendar(Request $request, CalendarController $calendar)
    {
        $data_calendar    = @json_decode($request['data']);
        $year             = @$data_calendar->year;
        $month            = @$data_calendar->month;
        $room_id          = @$data_calendar->room_id;
        $reservation_id   = @$data_calendar->reservation_id;

        $reservation_details = Reservation::find($reservation_id);

        if(!$room_id)
            $room_id      = $reservation_details->room_id;
        if($reservation_details->list_type == 'Experiences'){
            $host_experiences_controller = new HostExperiencesController;
            $data['calendar'] = $host_experiences_controller->get_calendar_view($room_id, $year, $month, 'small');
        }else{
            $data['calendar'] = $calendar->generate_small($room_id, $year, $month, $reservation_id);
        }

        return $data['calendar'];
    }

    /**
     * Remove Special Offer
     *
     * @param array $request  Input values
     * @return redirect to Conversation page
     */
    public function remove_special_offer(Request $request)
    {

        $id = $request->id;
        $special_offer  = SpecialOffer::find($id);        
        $reservation_id = $special_offer->reservation_id;
        $type           = $special_offer->type;
        $reservation_details = Reservation::find($reservation_id);
        // Already paid         
        $already = Reservation::where('special_offer_id',$id)->where('status','Accepted')->first();
        if($already)
        {
            $this->helper->flash_message('error', 'Already Booked');
            return redirect('messaging/qt_with/'.$reservation_id);
        } 
        $special_offer->delete();
        $get_message = Messages::where('special_offer_id',$id)->first();
        $messages = Messages::where('special_offer_id',$id)->delete();

        $result['count'] = $this->payment_helper->InstantMessageCount($reservation_details->user_id);
        $result['instant_message'] = $get_message->id;
        $result['reservation_id']  = $reservation_id; 
        $result['type'] ='remove';
        $result['inbox'] ='yes';
        $redis = \LRedis::connection();
        $redis->publish('chat', json_encode($result));
        $type_name = ($type=='pre-approval') ? 'Pre-Approval' : 'Special offer';

        //remove status reservation
        $reservation =Reservation::find($special_offer->reservation_id);

        if($reservation->type == 'contact')
        {
            if(!$reservation->special_offer || @$reservation->special_offer->type == 'special_offer')
            {
                $reservation->status             = Null;
                $reservation->created_at       = date('Y-m-d H:i:s');
                $reservation->save();
            }
        }

        $this->helper->flash_message('success', trans('messages.inbox.type_has_removed',['type'=>$type_name])); // Call flash message function
        return redirect('messaging/qt_with/'.$reservation_id);
    
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
        $sStartDate   = date('Y-m-d',strtotime($sStartDate));
        $sEndDate     = date('Y-m-d',strtotime($sEndDate));
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
     * Load Admin Resubmit message reason
     *
     * @param array $request  Input values
     * @return view Admin Resubmit View File
     */
    public function admin_message(Request $request)
    {
        Messages::where('reservation_id',$request->id)->where('user_to',Auth::id())->where('read','0')->update(['read' =>'1']);

        $data['messages'] = Messages::where('reservation_id',$request->id)->orderBy('created_at','desc')->get(); 

        if ($data['messages']->count() <= 0 ) {
            abort(404);
        }
        $data['title'] = 'Admin Messages';
        return view('users.admin_resubmit', $data);
    }

}
