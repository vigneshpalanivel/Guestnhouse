<?php

/**
 * Messages Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Messages
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use JWTAuth;
use Carbon\Carbon;

class Messages extends Model
{
     /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'messages';

    protected $appends = ['created_time','pending_count','archived_count','reservation_count','unread_count','stared_count','all_count','host_check','guest_check','inbox_thread_count','admin_name'];

    public function setAttribute($attribute, $value)
    {
        if(in_array($attribute,['read', 'star', 'archive'])) {
            $this->attributes[$attribute] = strval($value);
        }
        else {
            $this->attributes[$attribute] = $value;
        }
    }

    // Save Model values to database without Trigger any events
    public function saveQuietly(array $options = [])
    {
        return static::withoutEvents(function () use ($options) {
            return $this->save($options);
        });
    }
    
    // Get All Messages
    public static function all_messages($user_id)
    {
        return Messages::where('user_to', $user_id)->groupby('user_from','user_to')->orderBy('id','desc')->get();
    }

    // Join to User table
    public function user_details()
    {
        return $this->belongsTo('App\Models\User','user_from','id');
    }

    // Join to Reservation table
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation','reservation_id','id');
    }

    // Join to Rooms Address table
    public function rooms_address()
    {
        return $this->belongsTo('App\Models\RoomsAddress','room_id','room_id');
    }

    // Join to Rooms Address table
    public function rooms()
    {
        return $this->belongsTo('App\Models\Rooms','room_id','id');
    }

    // Join to Host Experiences table
    public function host_experience()
    {
        return $this->belongsTo('App\Models\HostExperiences','room_id','id')->with('city_details');
    }

    // Join to Special Offer table
    public function special_offer()
    {
        return $this->belongsTo('App\Models\SpecialOffer','special_offer_id','id');
    }

    // Join with users table
    public function users()
    {
        return $this->belongsTo('App\Models\User', 'user_to', 'id');
    }

    // Get Admin name
    public  function getAdminNameAttribute()
    {
        return Admin::first()->username;
    }

    // Get All Message Count
    public  function getAllCountAttribute()
    {
        return Messages::where('user_to', $this->attributes['user_to'])->where('message_type','!=',5)->count();
    }

    // Get Stared Message Count
    public  function getStaredCountAttribute()
    {
        return Messages::where('user_to', $this->attributes['user_to'])->where('star', '1')->where('message_type','!=',5)->count();
    }

    // Get Unread Message Count
    public  function getUnreadCountAttribute()
    {
        return Messages::where('user_to', $this->attributes['user_to'])->where('read', '0')->where('message_type','!=',5)->count();
    }

    // Get Reservation Message Count
    public  function getReservationCountAttribute()
    {
        return Messages::where('user_to', $this->attributes['user_to'] )->where('reservation_id','!=', 0)->where('message_type','!=',5)->count();
    }

    // Get Archived Message Count
    public  function getArchivedCountAttribute()
    {
        return Messages::where('user_to', $this->attributes['user_to'])->where('archive', '1')->where('message_type','!=',5)->count();
    }   

    // Get Pending Message Count
    public function getPendingCountAttribute()
    {
        if(session('get_token') != '') { 
            $user   = JWTAuth::toUser(session('get_token'));
            $user_id = $user->id;
        }
        else {
            $user_id = Auth::id();
        }

        return Reservation::join('messages', function($join) use($user_id) {
            $join->on('messages.reservation_id', '=', 'reservation.id')->where('reservation.status','Pending')->where('messages.user_to','=', $user_id)->where('message_type','!=',5);
        })->count();

    }

    // Host Check
    public function getHostCheckAttribute()
    {
        if(session('get_token') != '') { 
            $user = JWTAuth::toUser(session('get_token'));
            $check =  Reservation::where('room_id', $this->attributes['room_id'])->where('host_id', $user->id)->count();
        }
        else {
            $check =  Reservation::where('room_id', $this->attributes['room_id'])->where('host_id', Auth::id() )->count();
        }

        return ($check != 0) ? 1 : 0;
    }

    // Guest Check
    public function getGuestCheckAttribute()
    {
        if(session('get_token') != '') { 
            $user = JWTAuth::toUser(session('get_token'));
            $check =  Reservation::where('room_id', $this->attributes['room_id'])->where('host_id', $user->id)->count();
        }
        else {
            $check =  Reservation::where('room_id', $this->attributes['room_id'])->where('host_id', Auth::id() )->count();
        }

        return ($check == 0) ? 1 : 0;
    }

    // Get Unread Message Count for separate thread
    public  function getInboxThreadCountAttribute()
    {
        return Messages::where('user_to', $this->attributes['user_to'])->where('read', '0')->where('reservation_id',$this->attributes['reservation_id'])->count();
    }

    // Get Message type reason attribue
    public  function getMessageTypeReasonAttribute()
    {
        if($this->attributes['message_type'] == 13) {
            return trans('messages.inbox.resubmit_id_document_head');
        }
        return '';
    }

    public function getMessageTypeTextAttribute()
    {
        if($this->host_check != 1 && in_array(optional($this->reservation)->status,['Pending','Inquiry'])) {
            return "pending";
        }

        return "unread";
    }

    // Get Created at Time for Message
    public function getCreatedTimeAttribute()
    {
        $created_timestamp = strtotime($this->attributes['created_at']);
        $date_obj = Carbon::createFromTimestamp($created_timestamp);

        if(session('get_token') != '') {
            $user = JWTAuth::toUser(session('get_token'));
            $timezone = $user->timezone;
        }
        else {
            if(Auth::check()) {
                $timezone = Auth::user()->timezone;
            }
        }
        if(isset($timezone)) {
            $date_obj->setTimeZone($timezone);
        }

        $format = (date('d-m-Y') == date('d-m-Y',$created_timestamp)) ? 'h:i A' : PHP_DATE_FORMAT;

        return $date_obj->format($format);
    }
}