<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;

class MultipleReservation extends Model
{
    //
    protected $table = 'multiple_reservations';

    protected $appends = ['all_service_fee','all_host_fee'];

    // Join with multiple_rooms table
	public function multiple_rooms() {
		return $this->belongsTo('App\Models\MultipleRooms', 'multiple_room_id', 'id');
	}

	public function getDiscountsListAttribute() {
		$discounts_list = [];
		$length_of_stay_type = $this->attributes['length_of_stay_type'] ?: '';
		$booked_period_type = $this->attributes['booked_period_type'] ?: '';

		$k = 0;

		if ($booked_period_type != '') {
			$discounts_list[$k] = array();
			if ($booked_period_type == 'early_bird') {
				$type_text = trans('messages.rooms.early_bird_price_discount');
			} else if ($booked_period_type == 'last_min') {
				$type_text = trans('messages.rooms.last_min_price_discount');
			}

			$text = @$this->attributes['booked_period_discount'] . '% ' . $type_text;
			$discounts_list[$k]['text'] = $text;
			$discounts_list[$k]['price'] = @$this->getBookedPeriodDiscountPriceAttribute();
			$k++;
		}
		if ($length_of_stay_type != '') {
			$discounts_list[$k] = array();
			if ($length_of_stay_type == 'weekly') {
				$type_text = trans('messages.rooms.weekly_price_discount');
			} else if ($length_of_stay_type == 'monthly') {
				$type_text = trans('messages.rooms.monthly_price_discount');
			} else if ($length_of_stay_type == 'custom') {
				$type_text = trans('messages.rooms.long_term_price_discount');
			}

			$text = @$this->attributes['length_of_stay_discount'] . '% ' . $type_text;
			$discounts_list[$k]['text'] = $text;
			$discounts_list[$k]['price'] = @$this->getLengthOfStayDiscountPriceAttribute();
			$k++;
		}
		return $discounts_list;
	}

	public function getAdminDiscountsListAttribute() {
		$discounts_list = [];
		$length_of_stay_type = $this->attributes['length_of_stay_type'] ?: '';
		$booked_period_type = $this->attributes['booked_period_type'] ?: '';

		$k = 0;

		if ($booked_period_type != '') {
			$discounts_list[$k] = array();
			if ($booked_period_type == 'early_bird') {
				$type_text = trans('messages.rooms.early_bird_price_discount');
			} else if ($booked_period_type == 'last_min') {
				$type_text = trans('messages.rooms.last_min_price_discount');
			}

			$text = @$this->attributes['booked_period_discount'] . '% ' . $type_text;
			$discounts_list[$k]['text'] = $text;
			$discounts_list[$k]['price'] = @$this->getAdminBookedPeriodDiscountPriceAttribute();
			$k++;
		}
		if ($length_of_stay_type != '') {
			$discounts_list[$k] = array();
			if ($length_of_stay_type == 'weekly') {
				$type_text = trans('messages.rooms.weekly_price_discount');
			} else if ($length_of_stay_type == 'monthly') {
				$type_text = trans('messages.rooms.monthly_price_discount');
			} else if ($length_of_stay_type == 'custom') {
				$type_text = trans('messages.rooms.long_term_price_discount');
			}

			$text = @$this->attributes['length_of_stay_discount'] . '% ' . $type_text;
			$discounts_list[$k]['text'] = $text;
			$discounts_list[$k]['price'] = @$this->getAdminLengthOfStayDiscountPriceAttribute();
			$k++;
		}
		return $discounts_list;
	}

	public function getPerNightAttribute() {
		return $this->currency_calc('per_night');
	}
	public function getAdminPerNightAttribute() {
		return $this->admin_currency_calc('per_night');
	}

	public function getSubtotalAttribute() {
		return $this->currency_calc('subtotal');
	}
	public function getAdminSubtotalAttribute() {
		return $this->admin_currency_calc('subtotal');
	}

	public function getCleaningAttribute() {
		return $this->currency_calc('cleaning');
	}
	public function getAdminCleaningAttribute() {
		return $this->admin_currency_calc('cleaning');
	}

	public function getAdditionalGuestAttribute() {
		return $this->currency_calc('additional_guest');
	}
	public function getAdminAdditionalGuestAttribute() {
		return $this->admin_currency_calc('additional_guest');
	}

	public function getSecurityAttribute() {
		return $this->currency_calc('security');
	}
	public function getAdminSecurityAttribute() {
		return $this->admin_currency_calc('security');
	}

	public function getServiceAttribute() {
		return $this->currency_calc('service');
	}
	public function getAdminServiceAttribute() {
		return $this->admin_currency_calc('service');
	}

	public function getHostFeeAttribute() {
		return $this->currency_calc('host_fee');
	}
	public function getAdminHostFeeAttribute() {
		return $this->admin_currency_calc('host_fee');
	}

	public function getTotalAttribute() {
		return $this->currency_calc('total');
	}
	public function getAdminTotalAttribute() {
		return $this->admin_currency_calc('total');
	}

	public function getBasePerNightAttribute() {
		return $this->currency_calc('base_per_night');
	}
	public function getAdminBasePerNightAttribute() {
		return $this->admin_currency_calc('base_per_night');
	}

	public function getLengthOfStayDiscountPriceAttribute() {
		return $this->currency_calc('length_of_stay_discount_price');
	}
	public function getAdminLengthOfStayDiscountPriceAttribute() {
		return $this->admin_currency_calc('length_of_stay_discount_price');
	}

	public function getBookedPeriodDiscountPriceAttribute() {
		return $this->currency_calc('booked_period_discount_price');
	}
	public function getAdminBookedPeriodDiscountPriceAttribute() {
		return $this->admin_currency_calc('booked_period_discount_price');
	}

	public function getSubtotalMultiplyTextAttribute() {
		
		return $this->duration_text;

	}

	public function getDurationTextAttribute() {
		$duration = $this->duration;
		$duration_text = $duration . ' ' . trans_choice('messages.rooms.night', $duration);
		
		return $duration_text;
	}

	public function getDurationAttribute() {
		$duration = $this->attributes['nights'];

		return $duration;
	}

	public function getAllServiceFeeAttribute(){
		@$MultipleReservation = MultipleReservation::where('reservation_id',$this->attributes['reservation_id'])->sum('service');

		return @$MultipleReservation;
	}


	public function getAllHostFeeAttribute(){
		@$MultipleReservation = MultipleReservation::where('reservation_id',$this->attributes['reservation_id'])->sum('host_fee');

		return @$MultipleReservation;
	}

	// Calculation for current currency conversion of given price field
	public function currency_calc($field) {

		
		if (request()->segment(1) == 'api') {
			$user_details = JWTAuth::parseToken()->authenticate();

			$rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

			$usd_amount = $this->attributes[$field] / $rate;

			$api_currency = $user_details->currency_code;

			$default_currency = Currency::where('default_currency', 1)->first()->code;

			$session_rate = Currency::whereCode($user_details->currency_code != null ? $user_details->currency_code : $default_currency)->first()->rate;

			return round($usd_amount * $session_rate);

		} else {

			$rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

			$usd_amount = @$this->attributes[$field] / @$rate;

			$default_currency = Currency::where('default_currency', 1)->first()->code;

			$session_rate = Currency::whereCode((Session::get('currency')) ? Session::get('currency') : $default_currency)->first()->rate;

			return round($usd_amount * $session_rate);
		}

	}

	// Calculation for current currency conversion of given price field
	public function admin_currency_calc($field) {

		/*$route=@Route::getCurrentRoute();
			      if($route){
			        $api_url = @$route->getPath();
			      }else{
			        $api_url = '';
			      }

			      $url_array=explode('/',$api_url);
			        //Api currency conversion
		*/
		if (request()->segment(1) == 'api') {
			$user_details = JWTAuth::parseToken()->authenticate();

			$rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

			$usd_amount = $this->attributes[$field] / $rate;

			$api_currency = $user_details->currency_code;

			$default_currency = Currency::where('default_currency', 1)->first()->code;

			$session_rate = Currency::whereCode($user_details->currency_code != null ? $user_details->currency_code : $default_currency)->first()->rate;

			return round($usd_amount * $session_rate);

		} else {

			$rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

			$usd_amount = @$this->attributes[$field] / @$rate;

			$default_currency = Currency::where('default_currency', 1)->first()->code;

			$session_rate = Currency::whereCode($default_currency)->first()->rate;

			return round($usd_amount * $session_rate);
		}

	}
}
