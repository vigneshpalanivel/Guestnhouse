<?php

/**
 * Email Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Email
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Models\PasswordResets;
use App\Models\User;
use App\Models\Rooms;
use App\Models\MultipleRooms;
use App\Models\HostExperiences;
use App\Models\Disputes;
use App\Models\DisputeMessages;
use App\Models\Reservation;
use App\Models\SiteSettings;
use App\Models\PayoutPreferences;
use App\Models\ReferralSettings;
use App\Models\Currency;
use App\Models\Reviews;
use App\Models\Admin;
use App\Models\Language;
use Mail;
use Config;
use Auth;
use DateTime;
use DateTimeZone;
use App;
use JWTAuth;
use App\Mail\MailQueue;
use App\Http\Helper\PaymentHelper;


class EmailController extends Controller
{


    /**
     * Send Welcome Mail to Users with Confirmation Link
     *
     * @param array $user  User Details
     * @return true
     */
    public function welcome_email_confirmation($user)
    {
        $data['first_name'] = $user->first_name;
        $data['email'] = $user->getOriginal('email');
        $data['token'] = str_random(100);
        $data['type'] = 'welcome';
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $password_resets = new PasswordResets;

        $password_resets->email      = $user->getOriginal('email');
        $password_resets->token      = $data['token'];
        $password_resets->created_at = date('Y-m-d H:i:s');
        $password_resets->save();
        $data['subject'] = trans('messages.email.confirm_email_address',[],$data['locale']);
        $data['view_file'] = 'emails.email_confirm';

        Mail::to($data['email'], $data['first_name'])->queue(new MailQueue($data));
        return true;
    }


    /**
     * Send Welcome Mail to Users with Confirmation Link
     *
     * @param array $user  User Details
     * @return true
     */
    public function contact_email_confirmation($user_contact)
    {
        $admin = Admin::first();
        $data['admin_email'] = $admin->email;
        $data['admin_name'] =  'Admin';
        
        $data['contact_name'] = $user_contact->name;
        $data['contact_email'] = $user_contact->email;
        $data['contact_feedback'] = $user_contact->feedback;
        $data['url'] = url('/').'/';
        $data['locale']       = Language::where('default_language',1)->first()->value;

        $data['subject'] = trans('messages.email.contact_us_email');
        $data['view_file'] = 'emails.email_contact';
        
        Mail::to($data['admin_email'], $data['admin_name'])->queue(new MailQueue($data));
        return true;
    }

    /**
     * Send Forgot Password Mail with Confirmation Link
     *
     * @param array $user  User Details
     * @return true
     */
    public function forgot_password($user)
    {  
        $data['first_name'] = $user->first_name;

        $data['token'] = str_random(100);
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $password_resets = new PasswordResets;

        $password_resets->email      = $user->getOriginal('email');
        $password_resets->token      = $data['token'];
        $password_resets->created_at = date('Y-m-d H:i:s');
        
        $password_resets->save();

        $data['subject'] = trans('messages.email.reset_your_pass',[], $data['locale']);
        $data['view_file'] = 'emails.forgot_password';
        
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
        return true;
    }

    /**
     * Send Email Change Mail with Confirmation Link
     *
     * @param array $user  User Details
     * @return true
     */
    public function change_email_confirmation($user)
    {
        $data['first_name'] = $user->first_name;
        $data['token'] = str_random(100);
        $data['type'] = 'change';
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $password_resets = new PasswordResets;

        $password_resets->email      = $user->getOriginal('email');
        $password_resets->token      = $data['token'];
        $password_resets->created_at = date('Y-m-d H:i:s');

        $password_resets->save();

        $data['subject']=trans('messages.email.confirm_email_address',[],$data['locale']);
        $data['view_file'] = 'emails.email_confirm';

        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
        return true;
    }

    /**
     * Send New Email Change Mail with Confirmation Link
     *
     * @param array $user  User Details
     * @return true
     */
    public function new_email_confirmation($user)
    {
        $data['first_name'] = $user->first_name;
        $data['token'] = str_random(100);
        $data['type'] = 'confirm';
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $password_resets = new PasswordResets;

        $password_resets->email      = $user->getOriginal('email');
        $password_resets->token      = $data['token'];
        $password_resets->created_at = date('Y-m-d H:i:s');

        $password_resets->save();

        $data['subject']=trans('messages.email.confirm_email_address',[],$data['locale']);
        $data['view_file'] = 'emails.email_confirm';

        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
        return true;
    }

    /**
     * Send Inquiry Mail to Host
     *
     * @param array $reservation_id Contact Request Details
     * @return true
     */
    public function inquiry($reservation_id , $question)
    {
        $data['result'] = $reservation =  Reservation::find($reservation_id);
        $data['question'] = $question;
        $user = $data['result']->host_users;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();
        
        $data['subject'] = trans('messages.email.inquiry_at',[],$data['locale']).' '.$data['result']['rooms']['name'].' '.trans('messages.email.for',[],$data['locale']).' '.$data['result']['dates_subject'];
        $data['view_file'] = 'emails.inquiry';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'            => 'Chat',
            'type'           => 'Host',
            'title'          => 'Booking Inquiry',  
            'reservation_id' => $reservation_id, 
            'host_user_id'   => $reservation->user_id,    
            'message'        => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation,'host');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
        return true;
    }

    /**
     * Send Booking Mail to Host
     *
     * @param array $reservation_id Request Details
     * @return true
     */
    public function booking($reservation_id)
    {
        $data['result'] = Reservation::find($reservation_id);
        $user = $data['result']->host_users;
        $data['hide_header'] = true;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency', 'messages']);

        $data['result'] = $data['result']->first()->toArray();

        $data['subject'] = trans('messages.email.booking_inquiry_for',[],$data['locale']).' '.$data['result']['rooms']['name'].' '.trans('messages.email.for',[],$data['locale']).' '.$data['result']['dates_subject'];

        $data['view_file'] = 'emails.booking';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send itinerary Mail to Host
     *
     * @param string $reservation_id Reservation Code
     * @param string $email Friend Email
     * @return true
     */
    public function itinerary($code , $email )
    {
        $data['result'] = Reservation::where('code', $code)->first();
        $user = $data['result']->host_users;
        $data['hide_header'] = true;
        $data['email'] = $email;
        $data['url'] = url('/').'/';
        $data['map_key'] = view()->shared('map_key');
        $data['locale'] = $data['result']->users->user_email_language;
        $site_settings = SiteSettings::all();  
        $data['contact']=$site_settings[21]->value;
        $data['result'] = Reservation::where('reservation.id', $data['result']->id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms' => function($query) {
                $query->with('rooms_address');
            }, 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();
      
        $data['subject'] = trans('messages.email.an_itinerary_shared',[],$data['locale']);

        $data['view_file'] = 'emails.itinerary';
        Mail::to($data['email'], $data['subject'])->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send preapproval Mail to Host
     *
     * @param array $reservation_id Reservation Id
     * @param string $preapproval_message Message from Host when pre-approving
     * @param type for Checking Pre-approval or Special-Offer
     * @return true
     */
    public function preapproval($reservation_id, $preapproval_message, $type = 'pre-approval')
    {
        $data['result']   = $reservation   = Reservation::find($reservation_id);
        $user                        = $data['result']->users;
        $data['first_name']          = $user->first_name;
        $data['preapproval_message'] = $preapproval_message;
        $data['type']                = $type;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;
        $reservation_details = $data['result'];

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms' => function($query) {
                $query->with('rooms_address');
            }, 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency', 'special_offer' => function($query) {
                $query->orderby('special_offer.id','desc')->limit(1)->with('rooms');
            }]);

        $data['result'] = $data['result']->first()->toArray();
        
        if($type == 'pre-approval') {
            $subject = $data['result']['host_users']['first_name'].' '.trans('messages.email.reservation_itinerary_from',[],$data['locale']).' '.$data['result']['special_offer']['rooms']['name']." for ".$data['result']['special_offer']['dates_subject'];
        }
        else if($type == 'special_offer') {
            $subject = $data['result']['host_users']['first_name'].' '.trans('messages.email.sent_Special_Offer_for',[],$data['locale']).' '.$data['result']['special_offer']['rooms']['name']." for ".$data['result']['special_offer']['dates_subject'];
        }

        $data['subject'] = $subject;
        $data['view_file'] = 'emails.preapproval';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'            => 'Chat',
            'type'           => 'Guest',
            'title'          => 'Booking Pre-Approve', 
            'reservation_id' => $reservation_id,  
            'host_user_id'   => $reservation->host_id,  
            'message'        => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'guest');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
        return true;
    }

    /**
     * Send Listed Mail to Host
     *
     * @param array $room_id Room Details
     * @return true
     */
    public function listed($room_id)
    {
        $result               = Rooms::find($room_id);
        $user                 = $result->users;
        $data['first_name']   = $user->first_name;
        $data['room_name']    = $result->name;
        $data['created_time'] = $result->created_time;
        $data['room_id']      = $result->id;
        $data['url']          = url('/').'/';
        $data['locale'] = $user->user_email_language;   
        $data['subject'] = trans('messages.email.your_space_listed',[],$data['locale']).' '.SITE_NAME;
        $data['listed_room_type'] = $result->type;

        $data['multiple_rooms'] = [];
        if($result->type=='Multiple'){
          $data['multiple_rooms'] = $result->multiple_rooms;
        }
        
        $data['view_file'] = 'emails.listed';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send Unlisted Mail to Host
     *
     * @param array $room_id Room Details
     * @return true
     */
    public function unlisted($room_id)
    {
        $result = Rooms::find($room_id);
        $user = $result->users;
        $data['first_name'] = $user->first_name;
        $data['created_time'] = $result->created_time;
        $data['room_id'] = $result->id;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $data['subject'] = trans('messages.email.listing_deactivated',[],$data['locale']).' '.SITE_NAME.' '.trans('messages.email.account');

        $data['view_file'] = 'emails.unlisted';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send Updated Payout Information Mail to Host
     *
     * @param array $payout_preference_id Payout Preference Details
     * @return true
     */
    public function payout_preferences($payout_preference_id, $type = 'update')
    {
        if($type != 'delete') {
            $result = PayoutPreferences::find($payout_preference_id);
            $user = $result->users;
            $data['first_name'] = $user->first_name;
            $data['updated_time'] = $result->updated_time;
            $data['updated_date'] = $result->updated_date;
            $data['deleted_time'] = $result->deleted_time;
        }
        else {
            if(request()->segment(1) == 'api') {
                $user=JWTAuth::parseToken()->authenticate();
                $data['first_name'] = $user->first_name;
                $new_str = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone(Config::get('app.timezone')));
                $new_str->setTimeZone(new DateTimeZone($user->timezone));
            }
            else {
                $user = Auth::user();
                $data['first_name'] = $user->first_name;
                $new_str = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone(Config::get('app.timezone')));
                $new_str->setTimeZone(new DateTimeZone(Auth::user()->timezone));
            }
            $data['deleted_time'] = $new_str->format('d M').' at '.$new_str->format('H:i');
        }
        $data['type'] = $type;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        if($type == 'update')
            $subject = trans('messages.email.your',[],$data['locale']).' '.SITE_NAME." ".trans('messages.email.payout_information_updated',[],$data['locale']);
        else if($type == 'delete')
            $subject = trans('messages.email.your',[],$data['locale']).' '.SITE_NAME." ".trans('messages.email.payout_information_deleted',[],$data['locale']);
        else if($type == 'default_update')
            $subject = trans('messages.email.payout_information_changed',[],$data['locale']);
       
        $data['subject'] = $subject;
        $data['view_file'] = 'emails.payout_preferences';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
       
        return true;
    }

    /**
     * Send Need Payout Information Mail to Host/Guest
     *
     * @param array $reservation_id Reservation Details
     * @return true
     */
    public function need_payout_info($reservation_id, $type)
    {
        $result       = Reservation::find($reservation_id);
        $data['type'] = $type;
        
        if($type == 'guest') {
            $user = $result->users;
            $data['payout_amount'] = $result->admin_guest_payout;
        }
        else {
            $user = $result->host_users;
            $data['payout_amount'] = $result->admin_host_payout;
        }

        $data['currency_symbol'] = $result->currency->symbol;
        $data['first_name']      = $user->first_name;
        $data['user_id']         = $user->id;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;      
        $data['subject'] = trans('messages.email.information_needed',[],$data['locale']);

        $data['view_file'] = 'emails.need_payout_info';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Room Details Updated to Admin
     *
     * @param array $room_id, $content
     * @return true
     */
    public function room_details_updated($room_id, $field)
    {
        $data['room_id'] = $room_id;
        $data['result'] = Rooms::find($room_id)->toArray(); 
        $data['field'] = $field; 
        $data['user'] = User::find($data['result']['user_id']); 
        
        $data['url'] = url('/').'/';
        $data['locale']       = Language::where('default_language',1)->first()->value;

        $data['admin'] = Admin::whereStatus('Active')->first(); 
        $data['first_name'] = $data['admin']->username;
        $data['subject'] = trans('messages.email.rooms_details_updated',[], $data['locale']).' '.$data['result']['name'];
        if($data['result']['status'] == 'Listed') {
            $data['view_file'] = 'emails.room_details_updated';
            Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));
        }
        return true;
    }

     public function sub_room_details_updated($room_id, $field){

        $data['room_id'] = $room_id;
        $data['result'] = MultipleRooms::find($room_id)->toArray(); 
        $data['field'] = $field; 
        $data['user'] = User::find($data['result']['user_id']); 
        
        $data['url'] = url('/').'/';
        $data['locale']       = App::getLocale();

        $data['admin'] = Admin::whereStatus('Active')->first(); 
        $data['first_name'] = $data['admin']->username;
        $default_language = Language::where('default_language',1)->first();
        if($default_language){
            $data['locale'] = $default_language->value;
        }
        $data['subject'] = trans('messages.email.rooms_details_updated',[],  $data['locale']).' '.$data['result']['name'];
        if($data['result']['status'] == 'Listed'){
            $data['main_room_name'] = @Rooms::find($data['result']['room_id'])->name;
            /*Mail::queue('emails.room_details_updated', $data, function($message) use($data) {
                $message->to($data['admin']->email, $data['admin']->username)->subject($data['subject']);
            });*/
            
            $data['view_file'] = 'emails.sub_room_details_updated';
            
            Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));
        }
        return true;
    }

    public function listed_multiple($room_id)
    {
        $result               = MultipleRooms::find($room_id);
        if($result){
          $user                 = $result->rooms->users;
          $data['first_name']   = $user->first_name;
          $data['room_name']    = $result->name;
          $data['number_of_rooms']    = $result->number_of_rooms;
          $data['main_room_name']    = $result->rooms->name;
          
          $data['room_id']      = $result->id;
          $data['main_room_id']      = $result->room_id;
          $data['main_room_status']      = $result->rooms->status;
          $data['url']          = url('/').'/';
          // $data['locale']       = App::getLocale();   
          $data['locale'] = $user->user_email_language;
          $data['subject'] = trans('messages.email.your_space_listed',[],$data['locale']).' '.SITE_NAME;

          /*Mail::queue('emails.listed', $data, function($message) use($user, $data) {
              $message->to($user->email, $user->first_name)->subject($data['subject']);
          });*/
          $data['view_file'] = 'emails.listed_multiple';
          
          Mail::to($user->email, $user->first_name)->queue(new MailQueue($data));
        }

        return true;
    }

    /**
     * Send Unlisted Mail to Host
     *
     * @param array $room_id Room Details
     * @return true
     */
    public function unlisted_multiple($room_id)
    {
        $result = MultipleRooms::find($room_id);
        $user = $result->rooms->users;
        $data['first_name'] = $user->first_name;
        $data['created_time'] = $result->rooms->created_time;
        $data['room_id'] = $result->room_id;
        $data['main_room_name']    = $result->rooms->name;
        $data['room_name']    = $result->name;
        $data['number_of_rooms']    = $result->number_of_rooms;
        $data['url'] = url('/').'/';
        // $data['locale']       = App::getLocale();
        $data['locale'] = $user->user_email_language;

        $data['subject'] = trans('messages.email.listing_deactivated',[],$data['locale']).' '.SITE_NAME.' '.trans('messages.email.account',[],$data['locale']);

        /*Mail::queue('emails.unlisted', $data, function($message) use($user, $data) {
            $message->to($user->email, $user->first_name)->subject($data['subject']);
        });*/
        $data['view_file'] = 'emails.unlisted_multiple';
       
        Mail::to($user->email, $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send Need Payout Sent Mail to Host/Guest
     *
     * @param array $reservation_id Reservation Details
     * @return true
     */
    public function payout_sent($reservation_id, $type)
    {
        $data['result'] = Reservation::find($reservation_id);
        $data['type'] = $type;
        
        if($type == 'guest') {
            $user = $data['result']->users;
            $data['full_name'] = $data['result']->host_users->full_name;
            $data['payout_amount'] = $data['result']->admin_guest_payout;
            $payout_amount=html_entity_decode($data['result']['refund_currency']['symbol'], ENT_NOQUOTES, 'UTF-8').$data['payout_amount'];
        }
        else {
            $user = $data['result']->host_users;
            $data['full_name'] = $data['result']->users->full_name;
            $data['payout_amount'] = $data['result']->admin_host_payout;
            $payout_amount=html_entity_decode($data['result']['currency']['symbol'], ENT_NOQUOTES, 'UTF-8').$data['payout_amount'];
        }

        $data['result'] = Reservation::where('reservation.id',$reservation_id)->with(['rooms', 'currency'])->first()->toArray();
        $data['first_name'] = $user->first_name;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;      

        $data['subject'] = trans('messages.email.payout_of',[],$data['locale']).' '.$payout_amount." ".trans('messages.email.sent',[],$data['locale']);

        $data['view_file'] = 'emails.payout_sent';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Referral Email Share
     *
     * @param array $emails Friend Emails
     * @return true
     */
    public function referral_email_share($emails)
    {
        $user_id = Auth::user()->id;

        $data['result'] = $user = User::with(['profile_picture'])->whereId($user_id)->first()->toArray();

        $data['travel_credit'] = ReferralSettings::value(4);
        $data['symbol'] = Currency::first()->symbol;

        $data['url'] = url('/').'/';
        $data['locale'] = $user['user_email_language']; 

        $emails = explode(',', $emails);

        $data['subject'] = $user['full_name']." ".trans('messages.email.invited_you_to',[],$data['locale']).' '.SITE_NAME;
        foreach($emails as $email) {
            $email = trim($email);
            $data['view_file'] = 'emails.referral_email_share';
            Mail::to($email)->queue(new MailQueue($data));
        }
        return true;
    }

    /**
     * Review Remainder
     *
     * @param array $reservation
     * @param string $type
     * @return true
     */
    public function review_remainder($reservation, $type='guest')
    {
        $data['url'] = SiteSettings::where('name', 'site_url')->first()->value.'/';

        if($type == 'guest') {
            $email = $reservation->host_users->email;
            $user = $reservation->users;
        }
        else {
            $email = $reservation->users->email;
            $user = $reservation->host_users;
        }

        $data['users'] = $user;
        $data['result'] = $reservation->toArray();

        $data['locale'] = $user->user_email_language; 
        $data['profile_picture'] = $user->profile_picture->email_src;
        $data['review_name'] = $user->first_name;

        $data['subject'] = trans('messages.email.write_review_about',[],$data['locale'])." ".$user->first_name;
       
        $data['view_file'] = 'emails.review_remainder';
        Mail::to($email)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Review Wrote
     *
     * @param int $review_id
     * @param string $type
     * @return true
     */
    public function wrote_review($review_id, $type ='guest')
    {
        $data['url'] = url('/').'/';

        $reviews = Reviews::find($review_id);

        $data['locale'] = $reviews->users->user_email_language; 
        $email = $reviews->users->email;

        $user = $reviews->users_from;

        $data['users'] = $user;
        $data['result'] = $reviews->toArray();

        $data['review_end_date'] = Reservation::find($reviews->reservation_id)->review_end_date;

        $data['profile_picture'] = $user->profile_picture->src;
        $data['review_name'] = $user->first_name;

        $data['view_url']= Reservation::find($reviews->reservation_id)->review_link;
        $data['subject'] = $user->first_name.' '.trans('messages.email.wrote_you_review',[],$data['locale']);
        
        $data['view_file'] = 'emails.wrote_review';
        Mail::to($email)->queue(new MailQueue($data));
    }

    /**
     * Review Read
     *
     * @param int $review_id
     * @param string $type
     * @return true
     */
    public function read_review($review_id, $type ='guest')
    {
        $data['url'] = url('/').'/';
        $reviews = Reviews::find($review_id);
        $data['locale'] = $reviews->users->user_email_language;
        
        $email = $reviews->users->email;

        $user = $reviews->users_from;

        $data['users'] = $user;
        $data['result'] = $reviews->toArray();

        $data['review_end_date'] = Reservation::find($reviews->reservation_id)->review_end_date;

        $data['profile_picture'] = $user->profile_picture->src;
        $data['review_name'] = $user->first_name;
        $data['view_url']= Reservation::find($reviews->reservation_id)->review_link;
        $data['subject'] = trans('messages.email.read',[],$data['locale']).' '.$user->first_name."'s ".trans('messages.email.review',[],$data['locale']);

        $data['view_file'] = 'emails.read_review';
        Mail::to($email)->queue(new MailQueue($data));
    }

    /**
     * Send accepted Mail to Host
     *
     * @param string $reservation_code Reservation Code
     * @param string $email Friend Email
     * @return true
     */
    public function accepted($code)
    {
        $data['result']         = $reservation_details = Reservation::where('id', $code)->first();
        $user                   = $data['result']->users;
        $data['hide_header']    = true;
        
        $data['url']            = url('/').'/';
        $data['map_key']        = view()->shared('map_key');
       
        $site_settings = SiteSettings::all();  
        $data['contact']=$site_settings[21]->value;
        $data['result'] = Reservation::where('reservation.id', $data['result']->id)->with(['users' => function($query)  {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms' => function($query) {
                $query->with('rooms_address');
            }, 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();
        $data['locale'] = $data['result']['users']['user_email_language'];
        $data['subject'] = trans('messages.email.reservation_confirmed',[],$data['locale']).' '.$data['result']['host_users']['full_name'];

        $data['view_file'] = 'emails.accepted';
        // return view('emails.accepted',$data);

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => 'Guest',
            'title'            => 'Booking Accepted',  
            'reservation_id'   => $reservation_details->id, 
            'host_user_id'     => $reservation_details->host_id,       
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'guest');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($data['result']['users']['email'], '')->queue(new MailQueue($data));
        return true;
    }

     /**
     * Send accepted Mail to Host
     *
     * @param string $reservation_code Reservation Code
     * @param string $email Friend Email
     * @return true
     */
    public function pre_accepted($code )
    {
        $data['result']         = $reservation_details = Reservation::where('id', $code)->first();
        $user                   = $data['result']->users;
        $data['hide_header']    = true;
        
        $data['url']            = url('/').'/';
        $data['map_key']        = view()->shared('map_key');
        $site_settings = SiteSettings::all();  
        $data['contact']=$site_settings[21]->value;
        $data['result'] = Reservation::where('reservation.id', $data['result']->id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms' => function($query) {
                $query->with('rooms_address');
            }, 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();
        $data['locale'] = $data['result']['users']['user_email_language'];

        $data['subject'] = trans('messages.inbox.reservations',[],$data['locale']).' '.trans('messages.inbox.pre_accepted',[],$data['locale']).' '.$data['result']['host_users']['full_name'];
        
        $data['view_file'] = 'emails.pre_accepted';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => 'Guest',
            'title'            => 'Booking Pre-Accept', 
            'reservation_id'   => $reservation_details->id,   
            'host_user_id'     => $reservation_details->host_id,      
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'guest');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($data['result']['users']['email'],'')->queue(new MailQueue($data));

        return true;
    }

    /**
     * Booking Confirmed Email to Host
     *
     * @param array $reservation_id
     * @return true
     */
    public function booking_confirm_host($reservation_id)
    { 
        $data['result'] = $reservation_details = Reservation::find($reservation_id);
        $user = $data['result']->host_users;
        $data['hide_header'] = true;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;
        $data['results'] = Reservation::find($reservation_id);

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency', 'messages']);

        $data['result'] = $data['result']->first()->toArray();
        $data['subject'] = trans('messages.email.booking_confirmed',[], $data['locale'])." ".$data['result']['rooms']['name']." ".trans('messages.email.for',[], $data['locale'])." ".$data['result']['dates_subject'];

        $data['view_file'] = 'emails.booking_confirm_host';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => 'Host',
            'title'            => 'Booking Confirmed',  
            'reservation_id'   => $reservation_id,  
            'host_user_id'     => $reservation_details->user_id,       
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'host');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    public function booking_confirm_admin($reservation_id)
    {
        $data['result'] = Reservation::find($reservation_id);
        $user = $data['result']->host_users;
        $data['hide_header'] = true;
        $data['url'] = url('/').'/';
        $data['locale']       = Language::where('default_language',1)->first()->value;

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency', 'messages']);

        $data['result'] = $data['result']->first()->toArray();

        $data['admin'] = Admin::whereStatus('Active')->first(); 
        $check_in_time =  $data['result']['rooms']['check_in_time'];
        $check_out_time =  $data['result']['rooms']['check_out_time'];
        
        if($check_in_time == 'Flexible') {
            $data['check_in_time'] = 'Flexible';
        }
        else {
            $in_time =  $data['result']['rooms']['check_in_time'];
            $data['check_in_time'] = date("h:i A", strtotime("00-00-00 $in_time:00:00"));
        }

        if($check_out_time == 'Flexible') {
            $data['check_out_time'] = 'Flexible';
        }
        else {
            $out_time =  $data['result']['rooms']['check_out_time'];
            $data['check_out_time'] = date("h:i A", strtotime("00-00-00 $out_time:00:00"));
        }

        $data['subject'] = trans('messages.email.booking_confirmed',[], $data['locale']).' '.$data['result']['rooms']['name'].' '.trans('messages.email.for',[], $data['locale']).' '.$data['result']['dates_subject'];

        $data['view_file'] = 'emails.booking_confirm_admin';
        Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));

        return true;
    }

    public function cancel_guest($code)
    {
        $data['result']      = $reservation_details = Reservation::where('id', $code)->first();
       
        $user                   = $data['result']->host_users;
        $data['hide_header']    = true;
        
        $data['url']            = url('/').'/';
        $data['map_key']        = view()->shared('map_key');

        $data['result'] = Reservation::where('reservation.id', $data['result']->id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms' => function($query) {
                $query->with('rooms_address');
            }, 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();
        $data['admin'] = Admin::whereStatus('Active')->first(); 
    
        $check_in_time =  $data['result']['rooms']['check_in_time'];
        $check_out_time =  $data['result']['rooms']['check_out_time'];
        if($check_in_time == 'Flexible') {
            $data['check_in_time'] = 'Flexible';
        }
        else {
            $in_time =  $data['result']['rooms']['check_in_time'];
            $data['check_in_time'] = date("h:i A", strtotime("00-00-00 $in_time:00:00"));
        }

        if($check_out_time == 'Flexible') {
            $data['check_out_time'] = 'Flexible';
        }
        else {
            $out_time =  $data['result']['rooms']['check_out_time'];
            $data['check_out_time'] = date("h:i A", strtotime("00-00-00 $out_time:00:00"));
        }

        $data['locale'] = Language::where('default_language',1)->first()->value;
        $data['subject']= trans('messages.email.reservation_cancelled_by',[],$data['locale']).' '.$data['result']['users']['full_name'];
        $data['view_file'] = 'emails.guest_cancel_confirm_admin';
        Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));

        $data['locale'] = $user->user_email_language;
        $data['subject']= trans('messages.email.reservation_cancelled_by',[],$data['locale']).' '.$data['result']['users']['full_name'];
        $data['view_file'] = 'emails.guest_cancel_confirm_host';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => 'Host',
            'title'            => 'Guest Cancellation',   
            'reservation_id'   => $reservation_details->id,
            'host_user_id'     => $reservation_details->user_id,       
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'host');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    public function cancel_host($code)
    { 
        $data['result']         = $reservation_details = Reservation::where('id', $code)->first();
        $user                   = $data['result']->users;
        $data['hide_header']    = true;
        $data['url']            = url('/').'/';
        $data['map_key']        = view()->shared('map_key');

        $data['result'] = Reservation::where('reservation.id', $data['result']->id)->with(['users' => function($query) {
            $query->with('profile_picture')->with('users_verification')->with('reviews');
        }, 'rooms' => function($query) {
            $query->with('rooms_address');
        }, 'host_users' => function($query) {
            $query->with('profile_picture')->with('users_verification')->with('reviews');
        }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();
        $data['admin'] = Admin::whereStatus('Active')->first(); 
           
        $check_in_time =  $data['result']['rooms']['check_in_time'];
        $check_out_time =  $data['result']['rooms']['check_out_time'];
        
        if($check_in_time == 'Flexible') {
            $data['check_in_time'] = 'Flexible';
        }
        else {
            $in_time =  $data['result']['rooms']['check_in_time'];
            $data['check_in_time'] = date("h:i A", strtotime("00-00-00 $in_time:00:00"));
        }
        if($check_out_time == 'Flexible') {
            $data['check_out_time'] = 'Flexible';
        }
        else {
            $out_time =  $data['result']['rooms']['check_out_time'];
            $data['check_out_time'] = date("h:i A", strtotime("00-00-00 $out_time:00:00"));
        }
  
        $data['locale'] = Language::where('default_language',1)->first()->value;
        if($data['result']['status']=='Declined')
           $subjects=trans('messages.email.request_cancelled_by',[],$data['locale']);
        else
           $subjects=trans('messages.email.reservation_cancelled_by',[],$data['locale']);
        $data['subject'] = $subjects.' '.$data['result']['host_users']['full_name'];

        $data['view_file'] = 'emails.host_cancel_confirm_admin';
        Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));

        $data['locale'] = $user->user_email_language;
        if($data['result']['status']=='Declined') {
           $subjects=trans('messages.email.request_cancelled_by',[],$data['locale']);
           $push_title = 'Decline Booking';
           $key = 'Booking';
        }
        else {
           $subjects=trans('messages.email.reservation_cancelled_by',[],$data['locale']);
           $push_title = 'Host Cancel';
           $key = 'Cancel';
        }
        $data['subject'] = $subjects.' '.$data['result']['host_users']['full_name'];

        $data['view_file'] = 'emails.host_cancel_confirm_guest';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => 'Guest',
            'title'            =>  $push_title, 
            'reservation_id'   => $reservation_details->id,  
            'host_user_id'     => $reservation_details->host_id,     
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'guest');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
        return true;
    }

    public function reservation_expired_admin($reservation_id)
    {       
        $data['results'] = Reservation::find($reservation_id);
        $user = $data['results']->host_users;
        $data['hide_header'] = true;
        $data['url'] = SiteSettings::where('name', 'site_url')->first()->value;
        $data['locale'] = Language::where('default_language',1)->first()->value;

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification');
            }, 'rooms', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification');
            }, 'currency'])->first()->toArray();

        $data['admin'] = Admin::whereStatus('Active')->first(); 

     
        $check_in_time =  $data['result']['rooms']['check_in_time'];
        $check_out_time =  $data['result']['rooms']['check_out_time'];

        if($check_in_time == 'Flexible') {
            $data['check_in_time'] = 'Flexible';
        }
        else {
            $in_time =  $data['result']['rooms']['check_in_time'];
            $data['check_in_time'] = date("h:i A", strtotime("00-00-00 $in_time:00:00"));
        }
        
        if($check_out_time == 'Flexible') {
            $data['check_out_time'] = 'Flexible';
        }
        else {
            $out_time =  $data['result']['rooms']['check_out_time'];
            $data['check_out_time'] = date("h:i A", strtotime("00-00-00 $out_time:00:00"));
        }
        $data['subject'] = trans('messages.email.reservation_expired',[], $data['locale']).' '.$data['results']['rooms']['name']." for ".$data['result']['dates_subject'];

        $data['view_file'] = 'emails.cancel_confirm_admin';
        Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));

        return true;
    }

    public function reservation_expired_guest($reservation_id)
    { 
        $data['results'] = Reservation::find($reservation_id);
        $user = $data['results']->host_users;
        $data['hide_header'] = true;       
        $data['url'] = SiteSettings::where('name', 'site_url')->first()->value;
        $data['locale']       = $user->user_email_language;

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification');
            }, 'rooms', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification');
            }, 'currency'])->first()->toArray();

        $data['admin'] = Admin::whereStatus('Active')->first(); 
     
        $check_in_time =  $data['result']['rooms']['check_in_time'];
        $check_out_time =  $data['result']['rooms']['check_out_time'];

        if($check_in_time == 'Flexible') {
            $data['check_in_time'] = 'Flexible';
        }
        else { 
            $in_time =  $data['result']['rooms']['check_in_time'];
            $data['check_in_time'] = date("h:i A", strtotime("00-00-00 $in_time:00:00"));
        }
        
        if($check_out_time == 'Flexible') {
            $data['check_out_time'] = 'Flexible';
        }
        else {
            $out_time =  $data['result']['rooms']['check_out_time'];
            $data['check_out_time'] = date("h:i A", strtotime("00-00-00 $out_time:00:00"));
        }
        $data['subject'] = trans('messages.email.reservation_expired',[], $data['locale']).' '.$data['results']['rooms']['name']." for ".$data['result']['dates_subject'];

        $data['view_file'] = 'emails.reservation_expire_guest';
        Mail::to($data['results']['users']['email'], $data['results']['users']['first_name'])->queue(new MailQueue($data));

        return true;
    }

    public function booking_response_remainder($reservation_id, $hours)
    {
        $data['result'] = Reservation::find($reservation_id);

        $user = $data['result']->host_users;
        $data['hide_header'] = true;
        $data['hours'] = $hours;
        $data['url'] = SiteSettings::where('name', 'site_url')->first()->value.'/';
        $data['locale'] = $user->user_email_language;

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'rooms', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();

        $check_in_time =  $data['result']['rooms']['check_in_time'];
        $check_out_time =  $data['result']['rooms']['check_out_time'];

        if($check_in_time == 'Flexible') {
            $data['check_in_time'] = 'Flexible';
        }
        else {
            $in_time =  $data['result']['rooms']['check_in_time'];
            $data['check_in_time'] = date("h:i A", strtotime("00-00-00 $in_time:00:00"));
        }
        if($check_out_time == 'Flexible') {
            $data['check_out_time'] = 'Flexible';
        }
        else {
            $out_time =  $data['result']['rooms']['check_out_time'];
            $data['check_out_time'] = date("h:i A", strtotime("00-00-00 $out_time:00:00"));
        }
     
        $data['subject'] = trans('messages.email.booking_inquiry_expire',[],  $data['locale']).' '.$data['result']['rooms']['name'].' '.trans('messages.email.for',[],  $data['locale']).' '.$data['result']['dates_subject'];

        $data['view_file'] = 'emails.booking_response_remainder';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;

    }
    /**
     * Send Document Verified Successfully email to user
     *
     * @param array $user  User Details
     * @return true
     */
    public function document_verified($user)
    {
        $data['first_name'] = $user->first_name;
        $data['url'] = url('/').'/';
        $data['locale']       = App::getLocale();
        $data['subject']=trans('messages.email.document_verified');
        $data['view_file'] = 'emails.document_verified';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /*Disputes Email Functions*/

    /**
     * Send Dispute Requested mail
     *
     * @return bool
     * @param $dispute_id 
     **/
    function dispute_requested($dispute_id)
    {
        $dispute = Disputes::where('id', $dispute_id)->with('dispute_user')->first();
        if(!$dispute) {
            return true;
        }
        $dispute->set_user_or_dispute_user('DisputeUser');

        $dispute_message = $dispute->dispute_messages->first();
        $dispute_message->dispute->set_user_or_dispute_user('DisputeUser');

        $data['url'] = url('/').'/';
        $receiver_details   = $dispute_message->receiver_details;
          $data['locale'] = $receiver_details->user_email_language;
        $data['hide_header'] = true;

        $data['result'] = $dispute->reservation;

        $data['dispute'] = $dispute->toArray();
        $data['dispute_message'] = $dispute_message->toArray();
        $data['subject']  = $dispute_message['sub_text'];        

        $data['view_file'] = 'emails.dispute_requested';
        Mail::to($data['dispute']['dispute_user']['email'], $data['dispute']['dispute_user']['first_name'])->queue(new MailQueue($data));

        $admin = $data['admin'] = Admin::whereStatus('Active')->first();
        $data['subject']  = $dispute_message->admin_sub_text;

        $data['locale'] = Language::where('default_language',1)->first()->value;
        $data['view_file'] = 'emails.dispute_requested_admin';
        Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));

        return true;
    }
    
    /**
     * Send dispute conversation Email
     *
     * @return bool
     * @param $dispute_id 
     **/
    function dispute_admin_conversation($dispute_message_id)
    {
        $dispute_message = DisputeMessages::where('id', $dispute_message_id)->where(function($query){
            $query->where('message_by', 'Admin')->orWhere('message_for', 'Admin');
        })->first();

        if(!$dispute_message) {
            return true;
        }

        $data['url'] = url('/').'/';
        $data['locale']       = App::getLocale();  

        $data['dispute_message'] = $dispute_message;

        if($dispute_message['message_by'] == 'Admin')
        {
            $sender_details     = Admin::whereStatus('Active')->first();
            $receiver_details   = $dispute_message->receiver_details;
            $data['first_name'] = ucfirst($receiver_details->first_name);
            $data['firstname']  = ucfirst($sender_details->username);
            $data['link']       = 'dispute_details/'.$dispute_message->dispute_id;
             $data['locale'] = $receiver_details->user_email_language;
        }   
        else 
        {
            $sender_details     = $dispute_message->sender_details;
            $receiver_details   = Admin::whereStatus('Active')->first();
            $data['first_name'] = ucfirst($receiver_details->username);
            $data['firstname']  = ucfirst($sender_details->first_name);
            $data['link']       = ADMIN_URL.'/dispute/details/'.$dispute_message->dispute_id;
        }     
        $data['email']   = $receiver_details->email;

        $data['subject']    = trans('messages.disputes.user_sent_message_to_you', ['first_name' => $data['firstname']],$data['locale']);

        $data['view_file'] = 'emails.dispute_conversation_admin';
        Mail::to($data['email'], $data['first_name'])->queue(new MailQueue($data));

        return true;
    }
    /**
     * Send dispute closed Email
     *
     * @return bool
     * @param $dispute_id 
     **/
    function dispute_closed($dispute_id)
    {
        $dispute = Disputes::where('id', $dispute_id)->first();
        if(!$dispute) {
            return true;
        }
        $data['url'] = url('/').'/';

        $data['link'] = 'dispute_details/'.$dispute_id;

        $data['result'] = $dispute->reservation;
        $data['dispute'] = $dispute;
        $data['locale'] = $data['dispute']->dispute_user->user_email_language;

        foreach(['Host', 'Guest'] as $user) {    
            $data['subject']  = $dispute->dispute_by == $user ? trans('messages.disputes.your_dispute_request_closed') : trans('messages.disputes.dispute_request_closed_by_you');
            $data['final_dispute_data'] = $dispute->final_dispute_data;
            $data['dispute_currency']   = $dispute->currency;

            $data['view_file'] = 'emails.dispute_closed';
            Mail::to($data['dispute']->dispute_user->email, $data['dispute']->dispute_user->first_name)->queue(new MailQueue($data));
        }
        return true;
    }

    /*Host Experience Email Functions*/

    /**
     * Send Review Submit Mail to Host
     *
     * @param array $room_id Room Details
     * @return true
     */
    public function review_submited($room_id)
    {
        $result               = HostExperiences::with('user','city_details')->where('id',$room_id)->get()->first();
        $user                 = $result->users;
        $data['first_name']   = $user->first_name;
        $data['room_name']    = $result->name;
        $data['created_time'] = $result->created_time;
        $data['room_id']      = $result->id;
        $data['url']          = url('/').'/';
        $data['locale'] = $user->user_email_language;
        $data['city']      = $result->city_details->name;
        $data['enc_phone_number']=$user->primary_phone_number_protected;
        $data['subject'] = trans('messages.email.submit_subject',[],$data['locale']);

        $data['view_file'] = 'emails.host_experiences.review_submited';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send Review Approved Mail to Host
     *
     * @param array $room_id Room Details
     * @return true
     */
    public function review_approved($room_id)
    {
        $result               = HostExperiences::with('user','city_details')->where('id',$room_id)->get()->first();
        $user                 = $result->users;
        $data['first_name']   = $user->first_name;
        $data['room_name']    = $result->name;
        $data['room_id']      = $result->id;
        $data['view_url']      = $result->link;
        $data['url']          = url('/').'/';
        $data['locale'] = $user->user_email_language; 
        $data['subject'] = trans('messages.email.review_approve_subject',[],$data['locale']);

        $data['view_file'] = 'emails.host_experiences.review_approved';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send Review Rejected Mail to Host
     *
     * @param array $room_id Room Details
     * @return true
     */
    public function review_rejected($room_id)
    {
        $result               = HostExperiences::with('user','city_details')->where('id',$room_id)->get()->first();
        $user                 = $result->users;
        $data['first_name']   = $user->first_name;
        $data['room_name']    = $result->name;
        $data['room_id']      = $result->id;
        $data['view_url']      = $result->link;
        $data['url']          = url('/').'/';
        $data['locale'] = $user->user_email_language;
        $data['subject'] = trans('messages.email.review_reject_subject',[],$data['locale']);

        $data['view_file'] = 'emails.host_experiences.review_rejected';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send booking confirmed Mail to guest
     *
     * @param string $reservation_code Reservation Code
     * @param string $email Friend Email
     * @return true
     */
    public function experience_accepted($code)
    {
        $data['result']         = $reservation_details = Reservation::where('id', $code)->first();
        $user                   = $data['result']->host_users;
        $data['hide_header']    = true;
         
        $data['url']            = url('/').'/';
        $data['map_key']        = view()->shared('map_key');
        $data['locale'] = $user->user_email_language;
        $site_settings = SiteSettings::all();  
        $data['contact']=$site_settings[21]->value;
        $data['result'] = Reservation::where('reservation.id', $data['result']->id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'host_experiences' => function($query) {
                $query->with('host_experience_location');
            }, 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);

        $data['result'] = $data['result']->first()->toArray();
        $data['link']   =$data['result']['host_experiences']['link'];
        $data['subject'] = trans('messages.email.booking_confirmed',[],$data['locale']).' '.$data['result']['host_experiences']['title'];

        $data['view_file'] = 'emails.host_experiences.experience_accepted';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => 'Guest',
            'title'            => 'Experience Booking Accepted',  
            'reservation_id'   => $reservation_details->id, 
            'host_user_id'     => $reservation_details->host_id,      
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'guest');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($data['result']['users']['email'], '')->queue(new MailQueue($data));

        return true;
    }

    /**
     * Experience Booking Confirmed Email to Host
     *
     * @param array $reservation_id
     * @return true
     */
    public function experience_booking_confirm_host($reservation_id){ 
        $data['result'] = $reservation_details = Reservation::find($reservation_id);
        $user = $data['result']->host_users;
        $data['hide_header'] = true;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;
      

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'host_experiences', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency', 'messages']);

        $data['result'] = $data['result']->first()->toArray();
        $data['subject'] = trans('messages.email.booking_confirmed',[],$data['locale'])." ".$data['result']['host_experiences']['title']." ".trans('messages.email.for',[],$data['locale'])." ".$data['result']['dates_subject'];
        $data['view_file'] = 'emails.host_experiences.experience_book_host';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => 'Host',
            'title'            => 'Experience Booking Confirmed',  
            'reservation_id'   => $reservation_details->id,
            'host_user_id'     => $reservation_details->user_id,        
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation_details,'host');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Experience Booking Confirmed Email to Admin
     *
     * @param array $reservation_id
     * @return true
     */
    public function experience_booking_confirm_admin($reservation_id){
        $data['result'] = Reservation::find($reservation_id);
        $user = $data['result']->host_users;
        $data['hide_header'] = true;
        $data['url'] = url('/').'/';
        $data['locale'] = Language::where('default_language',1)->first()->value;

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'host_experiences', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency', 'messages']);

        $data['result'] = $data['result']->first()->toArray();

        $data['admin'] = Admin::whereStatus('Active')->first(); 
        

        $data['subject'] = trans('messages.email.booking_confirmed',[], null,  $data['locale']).' '.$data['result']['host_experiences']['title'].' '.trans('messages.email.for',[], null,  $data['locale']).' '.$data['result']['dates_subject'];

        $data['view_file'] = 'emails.host_experiences.experience_book_admin';
        Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Experience Booking Cancelled Email to Host, Guest and Admin
     *
     * @param array $reservation_id
     * @return true
     */
    public function experience_booking_cancelled($reservation_id)
    {
        $reservation = Reservation::where('id', $reservation_id)->with([
            'users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 
            'host_experiences' => function($query) {
                $query->with('host_experience_location');
                $query->with('city_details');
            }, 
            'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 
            'currency'
        ])->first();

        if(!$reservation) {
            return '';
        }
        
        $result                 = $reservation->toArray();
        $result['duration_text']= $reservation->duration_text;
        $result['duration']     = $reservation->duration;

        $data                   = compact('result');
        $data['url']            = url('/').'/';
        $data['map_key']        = view()->shared('map_key');
        $data['hide_header']    = true;
        $data['admin']          = Admin::whereStatus('Active')->first(); 
        
        $data['to_user']        = $reservation->users;
        $data['locale'] = $data['to_user']->user_email_language;

        $data['subject']  = trans('experiences.emails.reservation_for_experience_cancelled',['name' => $reservation->host_experiences->title],[],$data['locale']);

        $data['view_file'] = 'emails.host_experiences.booking_cancelled_guest';
        Mail::to($data['to_user']->email, $data['to_user']->first_name)->queue(new MailQueue($data));

        $data['to_user']        = $reservation->host_users;
        $data['locale'] = $data['to_user']->user_email_language;

        $data['view_file'] = 'emails.host_experiences.booking_cancelled_host';
        Mail::to($data['to_user']->email, $data['to_user']->first_name)->queue(new MailQueue($data));
        
        $data['locale'] = Language::where('default_language',1)->first()->value;
        $data['view_file'] = 'emails.host_experiences.booking_cancelled_admin';

        $user_data =array(
            'device_id'  => ($reservation->cancelled_by == 'Guest') ? $reservation->host_users->device_id : $reservation->users->device_id,
            'device_type' => ($reservation->cancelled_by == 'Guest') ? $reservation->host_users->device_type : $reservation->users->device_type,
        );
        $notification_data = array(
            'key'              => 'Chat',
            'type'             => ($reservation->cancelled_by == 'Guest') ? 'Host' : 'Guest',
            'title'            => 'Experience Booking Cancelled',  
            'reservation_id'   => $reservation->id,
            'host_user_id'     => ($reservation->cancelled_by == 'Guest')  ? $reservation->host_id : $reservation->user_id,        
            'message'          => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation,'host');
        $payment_helper->SendPushNotification($user_data,$notification_data);

        Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));
        
        return true;
    }

    /**
     * Send Payout Sent Mail to Host/Guest
     *
     * @param array $reservation_id Reservation Details
     * @return true
     */
    public function experience_payout_sent($reservation_id, $type)
    {
        $data['result'] = Reservation::find($reservation_id);
        $data['type'] = $type;
        
        if($type == 'guest') {
            $user = $data['result']->users;
            $data['full_name'] = $data['result']->host_users->full_name;
            $data['payout_amount'] = $data['result']->admin_guest_payout;
            $payout_amount=html_entity_decode($data['result']['refund_currency']['symbol'], ENT_NOQUOTES, 'UTF-8').$data['payout_amount'];
        }
        else{
            $user = $data['result']->host_users;
            $data['full_name'] = $data['result']->users->full_name;
            $data['payout_amount'] = $data['result']->admin_host_payout;
            $payout_amount=html_entity_decode($data['result']['currency']['symbol'], ENT_NOQUOTES, 'UTF-8').$data['payout_amount'];
        }

        $data['result'] = Reservation::where('reservation.id',$reservation_id)->with(['host_experiences', 'currency'])->first()->toArray();
        $data['first_name'] = $user->first_name;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;

        $data['subject'] = trans('messages.email.payout_of',[],$data['locale']).' '.$payout_amount." ".trans('messages.email.sent');
        
        $data['view_file'] = 'emails.host_experiences.payout_sent';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }

    /**
     * Send Inquiry Mail to Host
     *
     * @param array $reservation_id Reservation Details
     * @return true
     */
    public function experience_inquiry_mail($reservation_id, $question)
    {
        $data['result'] = $reservation = Reservation::find($reservation_id);
        $data['question'] = $question;
        $user = $data['result']->host_users;
        $data['url'] = url('/').'/';
        $data['locale'] = $user->user_email_language;
        

        $data['result'] = Reservation::where('reservation.id', $reservation_id)->with(['users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'host_experiences', 'host_users' => function($query) {
                $query->with('profile_picture')->with('users_verification')->with('reviews');
            }, 'currency']);
        $data['result'] = $data['result']->first()->toArray();
       
        $data['subject'] = trans('experiences.emails.message_from_user', ['first_name' => $data['result']['users']['first_name'] ],[],$data['locale']);
        
        $data['view_file'] = 'emails.host_experiences.inquiry';

        $user_data =array(
            'device_id'  => $user->device_id,
            'device_type' => $user->device_type,
        );
        $notification_data = array(
            'key'            => 'Chat',
            'type'           => 'Host',
            'title'          => 'Experience Contact Host',  
            'reservation_id' => $reservation_id, 
            'host_user_id'   => $reservation->user_id,    
            'message'        => $data['subject'],
        );
        $payment_helper = new PaymentHelper;
        $payment_helper->Socket($reservation,'host');
        $payment_helper->SendPushNotification($user_data,$notification_data);
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));
        return true;
    }

    /**
    * send listing awaiting for admin approval email to admin
    *
    * @param array $room_id
    * @return true
    */
    public function awaiting_approval_admin($room_id){
      $data['room_id'] = $room_id;
      $data['result'] = Rooms::find($room_id)->toArray(); 
      $data['user'] = User::find($data['result']['user_id']); 

      $data['url'] = url('/').'/';
      $data['locale'] = App::getLocale();

      $data['admin'] = Admin::whereStatus('Active')->first(); 
      $data['first_name'] = $data['admin']->username;
      $data['subject'] = trans('messages.email.awaiting_approval_admin',[], null,  $data['locale']);

      $data['view_file'] = 'emails.awaiting_approval_admin';

      //return view('emails.awaiting_approval_admin',$data);
      Mail::to($data['admin']->email, $data['admin']->username)->queue(new MailQueue($data));

      return true;
    }

    /**
     * send listing awaiting for admin approval email to host
     *
     * @param array $room_id
     * @return true
     */
    public function awaiting_approval_host($room_id){

        $data['room_id'] = $room_id;
        $data['result'] = Rooms::find($room_id)->toArray(); 
        $data['user'] = User::find($data['result']['user_id']); 
        
        $data['url'] = url('/').'/';
        $data['locale'] = App::getLocale();
        $data['subject'] = trans('messages.email.awaiting_approval_host',[], null,  $data['locale']);
        $data['view_file'] = 'emails.awaiting_approval_host';
        $data['first_name'] = $data['user']['first_name'];

        //return view('emails.awaiting_approval_host', $data);
        Mail::to($data['user']->email, $data['user']->first_name)->queue(new MailQueue($data));
     
        return true;
    }

    /**
     * send listing approved by admin email to host
     *
     * @param array $room_id Room Details
     * @return true
     */
    public function listing_approved_by_admin($room_id)
    {
        $result               = Rooms::find($room_id);
        $user                 = $result->users;
        $data['first_name']   = $user->first_name;
        $data['room_name']    = $result->name;
        $data['created_time'] = $result->created_time;
        $data['room_id']      = $result->id;
        $data['url']          = url('/').'/';
        $data['locale']       = App::getLocale();   
        $data['subject'] = trans('messages.email.your_space_listed').' '.SITE_NAME;

        $data['view_file'] = 'emails.listing_approved_by_admin';
        Mail::to($user->getOriginal('email'), $user->first_name)->queue(new MailQueue($data));

        return true;
    }
 
}
