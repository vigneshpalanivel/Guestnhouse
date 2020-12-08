<?php

/**
 * Messages Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Messages
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\Models\User;
use App\Models\Reservation;
use Validator;
use DB;
use Auth;
use DateTime;
use Session;
use JWTAuth;

class MessagesController extends Controller
{
	protected $payment_helper;

	public function __construct()
	{
		$this->payment_helper = resolve("App\Http\Helper\PaymentHelper");
		$this->helper = resolve("App\Http\Start\Helpers");
	}

    /**
     *Send Message
     *
     * @param  Get method inputs
     * @return Response in Json Format
     */
    public function send_message(Request $request)
    {
    	$rules = array( 
    		'host_user_id'    => 'required|exists:users,id',
    		'message_type'    => 'required|exists:message_type,id',
    		'reservation_id'  => 'required|exists:reservation,id',
    		'message'         => 'required'
    	);

    	if($request->list_type=="Experiences"){
    		$rules['room_id'] = 'required|exists:host_experiences,id';
    		$attributes = array('room_id'  => 'Experience Id'); 
    	}
    	else{
    		$rules['room_id'] = 'required|exists:rooms,id';
    		$attributes = array('room_id'  => 'Room Id'); 
    	}

    	$messages  = array('required' => ':attribute is required.');
    	$validator = Validator::make($request->all(), $rules, $messages, $attributes);

    	if($validator->fails()) {
    		return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first()
            ]);
    	} 

    	$user = JWTAuth::parseToken()->authenticate();
    	Session::put('get_token',$request->token); 
        //Prevent Host Sending Message to Host 
		if($user->id == $request->host_user_id) {
			return response()->json([
				'status_code'     => '0',
				'success_message' => 'You Can Not Send Messages to Your Own Reservation',
			]);
		}

		$user_id = $user->id;
		$host_id = $request->host_user_id;

		$reservation_details = Reservation::where('room_id',$request->room_id)
		->where(function ($query) use ($user_id,$host_id) {
			$query->where('host_id','=',$user_id)->orWhere('host_id', $host_id);
		})
		->where(function ($query) use ($user_id,$host_id) {
			$query->where('user_id','=',$user_id)->orWhere('user_id', $host_id);
		})
		->find($request->reservation_id);

    	if($reservation_details == '') {
    		return response()->json([
    			'status_code'     => '0',
    			'success_message' => 'Reservation details Mismatch',
    		]);
    	}

		$messages = new Messages;
		$messages->room_id        	= $request->room_id;
		$messages->reservation_id 	= $request->reservation_id;
		$messages->user_to        	= $request->host_user_id;
		$messages->user_from      	= $user->id;
		$messages->message        	= $this->helper->phone_email_remove($request->message); 
		$messages->message_type   	= $request->message_type;
		$messages->list_type   		= $request->list_type;
        $messages->saveQuietly(); 

        $guest_message = Messages::with('reservation','user_details')->where('id',$messages->id)->get();

        $instant_message = $guest_message->map(function($messages) {
          	$message = $this->payment_helper->message_data($messages);
          	$message['reservation'] = $this->payment_helper->reservation_data($messages->reservation,$messages->user_to);
          	$message['special_offer'] = $this->payment_helper->special_data($messages->special_offer);
          	return $message;
        });

        $instant_message_mobile = $guest_message->map(function($messages) {
          	$sender_profile =  $messages->user_details->profile_picture->only('src');
          	$message['message'] = $messages->message;
          	$message['user_from'] = $messages->user_from;
          	$message['time']    = date('H:i a');
          	$message['sender_profile'] = $sender_profile['src'];
          	return $message;
        });

		$result = [];
		$result['count'] = $this->payment_helper->InstantMessageCount($reservation_details->user_id);
		$result['instant_message'] = $instant_message[0];
		if(isset($request->emit_ios)){
			$result['instant_message_mobile'] = $instant_message_mobile[0];
		}
		$result['reservation_id']  = $request->reservation_id; 
		$result['type'] ='add';
		$result['inbox'] ='yes';
		$redis = \LRedis::connection();
		$redis->publish('chat', json_encode($result));

		$userDetails = User::find($request->host_user_id);
		$user_data = array(
			'device_id'  => $userDetails->device_id,
			'device_type' => $userDetails->device_type,
		);

		$message = $this->helper->phone_email_remove($request->message);

		$type = ($reservation_details->user_id == $user->id) ? 'Host' : 'Guest';
		$notification_data = array(
			'key'			 => 'Chat',
			'type'           => $type,
			'message'        => $message,        
			'title'          => 'New Message From '.ucfirst(Auth::user()->first_name),
			'reservation_id' => (string)$reservation_details->id,
			'host_user_id'   => (string)$user->id,
		);

		$push_notification = $this->payment_helper->SendPushNotification($user_data,$notification_data);

		return response()->json([
			'success_message' => 'Message Send Successfully',
			'status_code'     => '1',
			'message'         => $this->helper->phone_email_remove($request->message),
			'message_time'   => $messages->created_time
		]);
  	}
}