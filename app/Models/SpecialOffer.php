<?php

/**
 * SpecialOffer Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    SpecialOffer
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;

class SpecialOffer extends Model
{
    use CurrencyConversion;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'special_offer';

    public $timestamps = false;

    public $appends = ['dates_subject', 'checkin_arrive', 'checkout_depart', 'dates'];

    public function setCheckinAttribute($value)
    {
        $this->attributes['checkin'] = date('Y-m-d', strtotime($value));
    }

    public function setCheckoutAttribute($value)
    {
        $this->attributes['checkout'] = date('Y-m-d', strtotime($value));
    }

    public function rooms()
    {
      return $this->belongsTo('App\Models\Rooms','room_id','id');
    }

    // Join with currency table
    public function currency()
    {
      return $this->belongsTo('App\Models\Currency','currency_code','code');
    }

    // Join with messages table
    public function messages()
    {
      return $this->belongsTo('App\Models\Messages','id','special_offer_id');
    }

    public function getPriceAttribute()
    {
        return $this->currency_calc('price');
    }

    public function getIsBookedAttribute()
    {
         $booked_remove_offer = Reservation::where('special_offer_id',$this->attributes['id'])->count();
         if($booked_remove_offer)
         return false;
         else
         return true;
    }
    
    public function calendar() {
      return $this->hasMany('App\Models\Calendar', 'room_id', 'room_id');
    }

    // Get This reservation date is avaablie
    public function getAvablityAttribute()
    {
      $calendar_not_available = $this->calendar()->where('date','>=',$this->attributes['checkin'])->where('date', '<', $this->attributes['checkout'])->where('status', 'Not available')->get();
      if($calendar_not_available->count() > 0) {
        return 1;
      } 
      else {
        return 0;
      }
    }

    // Get Checkin Arrive Date in md format
    public function getCheckinArriveAttribute()
    {
      $checkin =  date(PHP_DATE_FORMAT, strtotime($this->attributes['checkin']));
      return $checkin;
    }

    // Get Checkout Depart Date in md format
    public function getCheckoutDepartAttribute()
    {
      $checkout =  date(PHP_DATE_FORMAT, strtotime($this->attributes['checkout']));
      return $checkout;
    }

    // Get Date for Email Subject
    public function getDatesSubjectAttribute()
    {
      return date(PHP_DATE_FORMAT, strtotime($this->attributes['checkin'])).' - '.date(PHP_DATE_FORMAT, strtotime($this->attributes['checkout']));
    }

    // Get Checkin and Checkout Dates
    public function getDatesAttribute()
    {
      return date(PHP_DATE_FORMAT, strtotime($this->attributes['checkin'])).' - '.date(PHP_DATE_FORMAT, strtotime($this->attributes['checkout']));
    }
}
