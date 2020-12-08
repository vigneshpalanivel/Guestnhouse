<?php

/**
 * Search Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Search
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\PropertyType;
use App\Models\RoomType;
use App\Models\Rooms;
use App\Models\RoomsPhotos;
use App\Models\HostExperiencePhotos;
use App\Models\HostExperiences;
use App\Models\RoomsAddress;
use App\Models\Amenities;
use App\Models\AmenitiesType;
use App\Models\Calendar;
use App\Models\HostExperienceCalendar;
use App\Models\HostExperienceCategories;
use App\Models\Currency;
use App\Http\Controllers\Controller;
use App\Http\Helper\PaymentHelper;
use App\Http\Start\Helpers;
use Session;
use DB;
use Auth;

class SearchController extends Controller
{
    protected $payment_helper; // Global variable for Helpers instance
    protected $helper;  // Global variable for instance of Helpers

    /**
     * Constructor to Set PaymentHelper instance in Global variable
     *
     * @param array $payment   Instance of PaymentHelper
     */
    public function __construct(PaymentHelper $payment)
    {
        $this->payment_helper = $payment;
        $this->helper = new Helpers;
        $this->map_server_key = view()->shared('map_server_key');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $current_refinement="Homes";

        /*HostExperiencePHPCommentStart*/
        if(!empty($request->input('current_refinement'))) {
            $current_refinement=@$request->input('current_refinement');
        }
        /*HostExperiencePHPCommentEnd*/

        $previous_currency = Session::get('search_currency');
        $deleted_currency = Session::get('deleted_currency');
        $currency = Session::get('currency');

        $checkin_date_format        = $request->input('checkin_date_format');
        $checkout_date_format      = $request->input('checkout_date_format');
        $php_date_format      = $request->input('php_date_format');

        // if dont have choose date , set default date 
        $data['checkin'] = '';
        $data['st_date'] = '';
        $data['lat']  = 0;
        $data['long'] = 0;
        $data['viewport'] = '';

        $full_address = $request->input('location');
        $address      = str_replace(" ", "+", "$full_address");
        $geocode      = @file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$this->map_server_key.'&address='.$address.'&sensor=false');
        $json         = json_decode($geocode);
        
        if(@$json->{'results'}) {
            $data['lat']  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
            $data['long'] = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
            $data['viewport'] = $json->{'results'}[0]->{'geometry'}->{'viewport'};
        }

        if(!empty($request->input('checkin_date_format'))){
            $data['st_date'] = date(PHP_DATE_FORMAT,$this->helper->custom_strtotime($checkin_date_format, $php_date_format));            
        }
        elseif(!empty($request->input('checkin')) && $this->helper->custom_strtotime($request->input('checkin'), $php_date_format)) {
            $data['st_date'] = date(PHP_DATE_FORMAT,$this->helper->custom_strtotime($request->input('checkin'), $php_date_format));
            $data['checkin'] = date(PHP_DATE_FORMAT,$this->helper->custom_strtotime($request->input('checkin'), $php_date_format));
        }

        $data['end_date'] = '';
        $data['checkout'] = '';
        if(!empty($request->input('checkout_date_format'))){
            $data['end_date'] = date(PHP_DATE_FORMAT, $this->helper->custom_strtotime($checkout_date_format, $php_date_format));
        }
        elseif(!empty($request->input('checkout')) && $this->helper->custom_strtotime($request->input('checkout'), $php_date_format)) {
            $data['end_date'] = date(PHP_DATE_FORMAT,$this->helper->custom_strtotime($request->input('checkout'), $php_date_format));
            $data['checkout'] = date(PHP_DATE_FORMAT,$this->helper->custom_strtotime($request->input('checkout'), $php_date_format));
        }   

        $data['location']           = $request->input('location');
        
        $data['guest']              = $request->input('guests')=='' ? 1 : $request->input('guests');
        $data['bedrooms']           = $request->input('bedrooms');
        $data['bathrooms']          = $request->input('bathrooms');
        $data['beds']               = $request->input('beds');
        $data['property_type']      = $request->input('property_type');
        $data['room_type']          = $request->input('room_type');
        $data['amenities']          = $request->input('amenities');
        $data['min_price']          = $request->input('min_price');
        $data['max_price']          = $request->input('max_price');
        $data['instant_book']       = $request->input('instant_book') ? $request->input('instant_book') : 0;
        
        $data['room_type']          = RoomType::dropdown();
        $data['room_types']         = RoomType::where('status','Active')->get();
        $data['property_type_dropdown']      = PropertyType::active_all();
        $data['amenities']          = Amenities::activeType()->active()->get();
        $data['amenities_type']     = AmenitiesType::active_all();
        
        $data['property_type_selected'] = explode(',', $request->input('property_type'));
        $data['room_type_selected'] = explode(',', $request->input('room_type'));
        $data['amenities_selected'] = explode(',', $request->input('amenities'));
        $data['currency_symbol']    = Currency::first()->symbol;
        $data['cat_type_selected'] = explode(',', $request->input('host_experience_category'));
        $data['default_min_price'] = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, $currency, MINIMUM_AMOUNT);
        $data['default_max_price'] = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, $currency, MAXIMUM_AMOUNT);

        if(!$data['min_price']) {
            $data['min_price'] = $data['default_min_price'];
            $data['max_price'] = $data['default_max_price'];
        }
        elseif($previous_currency) {
            $data['min_price'] = $this->payment_helper->currency_convert($previous_currency, $currency, $data['min_price']); 
            $data['max_price'] = $this->payment_helper->currency_convert($previous_currency, $currency, $data['max_price']); 
        }
        elseif($deleted_currency) {
            $data['min_price'] = $data['default_min_price'];
            $data['max_price'] = $data['default_max_price'];
        }
        else {
            $data['min_price'] = $this->payment_helper->currency_convert('', $currency, $data['min_price']);
            $data['max_price'] = $this->payment_helper->currency_convert('', $currency, $data['max_price']);
        }
        $data['max_price_check'] = $this->payment_helper->currency_convert('', DEFAULT_CURRENCY, $data['max_price']);
        $data['current_refinement']=$current_refinement;
        Session::forget('search_currency');
        if($current_refinement == "Homes") {
            if($data['checkin'] != '' && $data['checkin'] == $data['checkout']){
                $data['checkout'] = date(PHP_DATE_FORMAT,$this->helper->custom_strtotime($data['checkin'].'+1 day'));
            }
            return view('search.search', $data);
        }

        $data['end_date'] = $data['st_date'];
        $data['guest'] = $data['guest'] > 10 ? 10 : $data['guest'];
        $data['host_experience_categories'] = HostExperienceCategories::where('status','Active')->get();
        return view('host_experiences.search', $data);
    }

    /**
     * Ajax Search Result for Experience
     *
     * @param array $request Input values
     * @return json Search results for Experiences
     */
    function searchexperienceResult(Request $request)
    {
        $previous_currency = Session::get('previous_currency');
        $currency = Session::get('currency');
        $full_address  = $request->input('location');
      
        $checkin       = $request->input('checkin');
        $checkout      = $request->input('checkout');
        $guest         = $request->input('guest');
        $host_experience_category = $request->input('host_experience_category');
        $min_price     = $request->input('min_price');
        $max_price     = $request->input('max_price');
        $map_details   = $request->input('map_details');
        
        $data['viewport'] = '';

        if(!$min_price) {
            $min_price = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, '', 0);
            $max_price = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, '', MAXIMUM_AMOUNT);
        }
        
        if(!is_array($host_experience_category)) {
            $host_experience_category = [];
            if($host_experience_category != '') {
                $host_experience_category = explode(',', $host_experience_category);
            }
        }
        
        $property_type_val   = [];
        $category_val   = [];
        $rooms_whereIn       = [];
        $room_type_val       = [];
        $rooms_address_where = [];
        
        $address      = str_replace([" ","%2C"], ["+",","], "$full_address");
        $geocode      = @file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$this->map_server_key.'&address='.$address.'&sensor=false&libraries=places');
        $json         = json_decode($geocode);
        
        if(@$json->results) {
            foreach($json->results as $result) {
                foreach($result->address_components as $addressPart) {
                    if((in_array('locality', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                        $city1 = $addressPart->long_name;
                        $rooms_address_where['host_experience_location.city'] = $city1;
                    }
                    if((in_array('administrative_area_level_1', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                        $state = $addressPart->long_name;
                        $rooms_address_where['host_experience_location.state'] = $state;
                    }
                    if((in_array('country', $addressPart->types)) && (in_array('political', $addressPart->types))) {
                        $country = $addressPart->short_name;
                        $rooms_address_where['host_experience_location.country'] = $country;
                    }
                }
            }
        }

        if($map_details != '') {
            $map_detail =   explode('~', $map_details);
            $zoom       =   $map_detail[0];
            $bounds     =   $map_detail[1];
            $minLat     =   $map_detail[2];
            $minLong    =   $map_detail[3];
            $maxLat     =   $map_detail[4];
            $maxLong    =   $map_detail[5];
            $cLat       =   $map_detail[6]; 
            $cLong      =   $map_detail[7];

            if($minLong>$maxLong){
                if($maxLong > 0){
                    $maxLong = $minLong;
                    $minLong = "-180"; 
                }else{
                    $maxLong = "180";
                }
            }
        }
        else {
            if(@$json->{'results'}) {
                $data['viewport'] = $json->{'results'}[0]->{'geometry'}->{'viewport'};

                $minLat = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'southwest'}->{'lat'};
                $maxLat = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'northeast'}->{'lat'};
                $minLong = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'southwest'}->{'lng'};
                $maxLong = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'northeast'}->{'lng'};
            }
            else {
                $data['lat'] = 0;
                $data['long'] = 0;

                $minLat = -1000;
                $maxLat = 1000;
                $minLong = -1000;
                $maxLong = 1000;
            }
        }
        $users_where['users.status']    = 'Active';

        $checkin  = date('Y-m-d', $this->helper->custom_strtotime($checkin));
        $checkout = date('Y-m-d', $this->helper->custom_strtotime($checkout));
        
        $days     = $this->get_days($checkin, $checkout);

        $calendar_where['date'] = $days;

        $not_available_room_ids = HostExperienceCalendar::whereIn('date', $days)->whereStatus('Not available')->distinct()->pluck('host_experience_id');

        $rooms_where['host_experiences.number_of_guests'] = $guest ? $guest : 1;
        
        $rooms_where['host_experiences.status']       = 'Listed';
        $rooms_where['host_experiences.admin_status']       = 'Approved';
            
        
        if(count($host_experience_category)) {                    
            foreach($host_experience_category as $category_value) {
                array_push($category_val, $category_value);
            }
        }        

        $currency_rate = Currency::where('code', Currency::first()->session_code)->first()->rate;

        $max_price_check = $this->payment_helper->currency_convert('', DEFAULT_CURRENCY, $max_price);

        $rooms = HostExperiences::with(['host_experience_location' => function($query) use($minLat, $maxLat, $minLong, $maxLong) { },
                            'currency' => function($query){},
                            'category_details' => function($query){},
                            'user' => function($query) use($users_where) {
                                $query->with('profile_picture')
                                      ->where($users_where);
                            },
                            'saved_wishlists' => function($query) {
                                $query->where('user_id', @Auth::user()->id)->where('list_type','Experiences');
                            }])
                            ->whereHas('host_experience_location', function($query) use($minLat, $maxLat, $minLong, $maxLong) {
                                $query->whereRaw("latitude between $minLat and $maxLat and longitude between $minLong and $maxLong");
                            })
                            ->whereHas('user', function($query) use($users_where) {
                                $query->where($users_where);
                            })
                            ->daysAvailable($days, $guest);
        if($rooms_where) {
            foreach($rooms_where as $row=>$value) {
                $operator = '=';
                if($row == 'host_experiences.number_of_guests')
                    $operator = '>=';                    

                if($value == '') {
                    $value = 0;
                }

                $rooms = $rooms->where($row, $operator, $value);
            }
        }
        if(count($host_experience_category)) {                    
            $rooms = $rooms->where(function($query) use($category_val) {
                $query->whereIn('category',$category_val);
                $query->orwhereIn('secondary_category',$category_val);
            });
        }
        $rooms = $rooms->orderByRaw('RAND(1234)')->paginate(18)->toJson();
        return response($rooms);
    }

    /**
     * Ajax Search Result
     *
     * @param array $request Input values
     * @return json Search results
     */
    public function searchResult(Request $request)
    {
        $full_address  = $request->input('location');
        $map_details   = $request->input('map_details');
        $checkin       = $request->input('checkin');
        $checkout      = $request->input('checkout');
        $guest         = $request->input('guest');
        $bathrooms     = $request->input('bathrooms');
        $bedrooms      = $request->input('bedrooms');
        $beds          = $request->input('beds');
        $property_type = $request->input('property_type');
        $room_type     = $request->input('room_type');
        $amenities     = $request->input('amenities');
        $min_price     = $request->input('min_price');
        $max_price     = $request->input('max_price');
        $instant_book  = $request->input('instant_book');

        $previous_currency = Session::get('previous_currency');
        $currency = Session::get('currency');

        if(!$min_price) {
            $min_price = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, '', 0);
            $max_price = $this->payment_helper->currency_convert(DEFAULT_CURRENCY, '', MAXIMUM_AMOUNT);
        }

        if(!is_array($room_type)) {
            $room_type = [];
            if($room_type != '') {
                $room_type = explode(',', $room_type);
            }             
        }
        
        if(!is_array($property_type)) {
            $property_type = [];
            if($property_type != '') {
                $property_type = explode(',', $property_type);
            }
        }

        if(!is_array($amenities)) {
            $amenities = [];
            if($amenities != '') {
                $amenities = explode(',', $amenities);             
            }
        }

        $property_type_val   = [];
        $room_type_val       = [];
        $rooms_whereIn       = [];
        
        $data['viewport'] = '';
        $address      = str_replace([" ","%2C"], ["+",","], "$full_address");
        $geocode      = @file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$this->map_server_key.'&address='.$address.'&sensor=false&libraries=places');
        $json         = json_decode($geocode);
    
    
        if($map_details != '') {
            $map_detail =   explode('~', $map_details);
            $zoom       =   $map_detail[0];
            $bounds     =   $map_detail[1];
            $minLat     =   $map_detail[2];
            $minLong    =   $map_detail[3];
            $maxLat     =   $map_detail[4];
            $maxLong    =   $map_detail[5];
            $cLat       =   $map_detail[6]; 
            $cLong      =   $map_detail[7];

            if($minLong>$maxLong) {
                if($maxLong > 0){
                    $maxLong = $minLong;
                    $minLong = "-180"; 
                }else{
                    $maxLong = "180";
                }
            }
        }
        else {
            if(@$json->{'results'}) {
                foreach ($json->{'results'}[0]->{'address_components'} as $value) {
                    if($value->types[0] == 'country') {
                        $country_code = $value->short_name;
                    }
                }

                if($json->{'results'}[0]->{'types'}[0] == 'country') {
                    $country_code = $json->{'results'}[0]->{'address_components'}[0]->{'short_name'};
                }
                $data['viewport'] = $json->{'results'}[0]->{'geometry'}->{'viewport'};

                $minLat = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'southwest'}->{'lat'};
                $maxLat = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'northeast'}->{'lat'};
                $minLong = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'southwest'}->{'lng'};
                $maxLong = $json->{'results'}[0]->{'geometry'}->{'viewport'}->{'northeast'}->{'lng'};
            }
            else {
                $data['lat'] = 0;
                $data['long'] = 0;

                $minLat = -1000;
                $maxLat = 1000;
                $minLong = -1000;
                $maxLong = 1000;
            }
        }

        $checkin  = date('Y-m-d', $this->helper->custom_strtotime($checkin));
        $checkout = date('Y-m-d', $this->helper->custom_strtotime($checkout));

        $dates_available = ($request->input('checkin') !='');
        
        $days     = $this->get_days($checkin, $checkout);
        unset($days[count($days)-1]);
        $total_nights = count($days);

        $total_weekends = 0;
        foreach($days as $day) {
            $weekday = date('N', strtotime($day));
            if( in_array($weekday, [5,6]) ) {
                $total_weekends++;
            }
        }
        $from                               = new \DateTime($checkin);
        $today                              = new \DateTime(date('Y-m-d'));
        $period                             = $from->diff($today)->format("%a")+1;
        $total_guests                       = $guest-0;
        $dates                              = implode(',', $days);

        $users_where['users.status']    = 'Active';

        $rooms_where['rooms.accommodates'] = $guest;
        $rooms_where['rooms.status']       = 'Listed';
            
        if($bathrooms)
            $rooms_where['rooms.bathrooms'] = $bathrooms;
            
        if($bedrooms)
            $rooms_where['rooms.bedrooms']  = $bedrooms;

        if($instant_book == 1)
            $rooms_where['rooms.booking_type'] = 'instant_book';
        
        $property_type = array_values($property_type);
        if(count($property_type)) {                    
            $rooms_whereIn['rooms.property_type'] = $property_type;
        }
        
        $room_type = array_values($room_type);
        if(count($room_type)) {
            $rooms_whereIn['rooms.room_type'] = $room_type;
        }
        
        $currency_rate = Currency::where('code', Currency::first()->session_code)->first()->rate;

        $max_price_check = $this->payment_helper->currency_convert('', DEFAULT_CURRENCY, $max_price);

        $not_available_room_ids = [];

        // Availability Filters Start
        $not_available_room_ids = Calendar::daysNotAvailable($days, $total_guests)->distinct()->pluck('room_id')->toArray();
        if($dates_available) {
            // Create virtual Table for rooms availability rules with given dates
            $availability_rules_virtual_table   = DB::table('rooms_availability_rules')
                                                ->select('minimum_stay as rule_minimum_stay', 'maximum_stay as rule_maximum_stay', 'room_id', 'id as rule_id')
                                                ->whereRaw("start_date <= '".$checkin."'")
                                                ->whereRaw("end_date >= '".$checkin."'")
                                                ->orderBy('type','ASC')
                                                ->orderBy('rooms_availability_rules.id','DESC')
                                                ->limit(1)
                                                ->toSql();
            // Query to get the prioritized rule minimum stay
            $rule_minimum_stay_query            =  DB::table('rooms_availability_rules')
                                                ->select('minimum_stay')
                                                ->whereRaw("start_date <= '".$checkin."'")
                                                ->whereRaw("end_date >= '".$checkin."'")
                                                ->orderBy('type','ASC')
                                                ->orderBy('rooms_availability_rules.id','DESC')
                                                ->whereRaw('room_id = rooms.id')
                                                ->limit(1)
                                                ->toSql();
            // Query to get the prioritized rule maximum stay
            $rule_maximum_stay_query            =  DB::table('rooms_availability_rules')
                                                ->select('maximum_stay')
                                                ->whereRaw("start_date <= '".$checkin."'")
                                                ->whereRaw("end_date >= '".$checkin."'")
                                                ->orderBy('type','ASC')
                                                ->orderBy('rooms_availability_rules.id','DESC')
                                                ->whereRaw('room_id = rooms.id')
                                                ->limit(1)
                                                ->toSql();
            // Query to get the prioritized rule id
            $rule_id_query                      =  DB::table('rooms_availability_rules')
                                                ->select('id')
                                                ->whereRaw("start_date <= '".$checkin."'")
                                                ->whereRaw("end_date >= '".$checkin."'")
                                                ->orderBy('type','ASC')
                                                ->orderBy('rooms_availability_rules.id','DESC')
                                                ->whereRaw('room_id = rooms.id')
                                                ->limit(1)
                                                ->toSql();
            // select availability rules virttual table with rooms table and select minimum and maximum stay values
            $rooms_availability_rules           = DB::table('rooms')
                                                ->select('rooms.id', 'rooms_price.minimum_stay', 'rooms_price.maximum_stay')
                                                ->selectRaw("(".$rule_minimum_stay_query.") as rule_minimum_stay")
                                                ->selectRaw("(".$rule_maximum_stay_query.") as rule_maximum_stay")
                                                ->selectRaw("(".$rule_id_query.") as rule_id")
                                                ->selectRaw('( SELECT IF(rule_id >0,(IFNULL(rule_minimum_stay, null)),(IFNULL(minimum_stay, null))) ) as check_minimum_stay')
                                                ->selectRaw('( SELECT IF(rule_id >0,(IFNULL(rule_maximum_stay, null)),(IFNULL(maximum_stay, null))) ) as check_maximum_stay')
                                                // ->leftJoin(DB::raw("(".$availability_rules_virtual_table.") as availability_rule"), 
                                                //     function($join) {
                                                //         $join->on('rooms.id','=','availability_rule.room_id');
                                                //     })
                                                ->join('rooms_price', 'rooms_price.room_id' ,'=', 'rooms.id')
                                                ->whereNotIn('rooms.id', $not_available_room_ids);
            // Compare the minimum stay and maximum stay value with the total nights to get the unavailable room_ids
            $availability_rules_missed_rooms    = $rooms_availability_rules
                                                ->havingRaw('(check_minimum_stay IS NOT NULL and check_minimum_stay > '.$total_nights.')')
                                                ->orHavingRaw('(check_maximum_stay IS NOT NULL and check_maximum_stay < '.$total_nights.')')
                                                ->pluck('id')->toArray();

            $not_available_room_ids = array_merge($not_available_room_ids, $availability_rules_missed_rooms);
        }
        // Availability Filters End
        // Basic Filters Start
        $rooms = Rooms::with(['rooms_address',
                    'users' => function($query) {
                        $query->with('profile_picture');
                    },
                    'saved_wishlists' => function($query) {
                        $query->where('user_id', @Auth::user()->id)->where('list_type','Rooms');
                    }])
                    ->whereHas('rooms_address', function($query) use($minLat, $maxLat, $minLong, $maxLong) {
                         $query->whereRaw("latitude between $minLat and $maxLat and longitude between $minLong and $maxLong");
                    })
                    ->whereHas('users', function($query) use($users_where) {
                        $query->where($users_where);
                    });
        if(@$country_code != ''){
            $rooms = $rooms->whereHas('rooms_address', function($query) use($country_code) {
                $query->where('country',$country_code);
            });
        }

        if($rooms_where) {
            foreach($rooms_where as $row=>$value) {
                $operator = '=';
                if($row == 'rooms.accommodates' || $row == 'rooms.bathrooms' || $row == 'rooms.bedrooms' || $row == 'rooms.beds')
                    $operator = '>=';

                if($value == '') {
                    $value = 0;
                }

                $rooms = $rooms->where($row, $operator, $value);
            }
        }

        if($rooms_whereIn) {
            foreach($rooms_whereIn as $row_rooms_whereIn => $value_rooms_whereIn) {
                $rooms = $rooms->whereIn($row_rooms_whereIn, array_values($value_rooms_whereIn));
            }
        }

        if(count($amenities)) {
            foreach($amenities as $amenities_value) {
                $rooms = $rooms->whereRaw('find_in_set('.$amenities_value.', amenities)');
            }
        }

        $rooms->whereNotIn('id', $not_available_room_ids);
        // Basic Filters End
        // 
        // Price Filter Start
        // Query to get sum of calendar price for rooms in a given dates
        $calendar_price_total_query         = DB::table("calendar")
                                            ->selectRaw('sum(price)')
                                            ->whereRaw('calendar.room_id = rooms.id')
                                            ->whereRaw('FIND_IN_SET(calendar.date, "'.$dates.'")')
                                            ->toSql();
        // Query to count the total calendar result for rooms as special nights
        $calendar_special_nights_query      = DB::table("calendar")
                                            ->selectRaw("count('*')")
                                            ->whereRaw('calendar.room_id = rooms.id')
                                            ->whereRaw('FIND_IN_SET(calendar.date, "'.$dates.'")')
                                            ->toSql();
        // Query to count the total weekend calendar result for rooms as special weekends
        $calendar_special_weekends_query    = DB::table("calendar")
                                            ->selectRaw("count('*')")
                                            ->whereRaw('calendar.room_id = rooms.id')
                                            ->whereRaw('FIND_IN_SET(calendar.date, "'.$dates.'")')
                                            ->whereRaw('( WEEKDAY(date) = 4 OR WEEKDAY(date) = 5 )')
                                            ->toSql();
        // Query to get rooms price rules minimum period for last min booking
        $min_price_rule_period_query        = DB::table('rooms_price_rules')
                                            ->selectRaw('min(period)')
                                            ->whereRaw('room_id = rooms.id')
                                            ->whereRaw('period>='.$period)
                                            ->whereRaw("type = 'last_min'")
                                            ->toSql();
        // Query to get rooms price rules maximum period for early bird booking
        $max_price_rule_period_query        = DB::table('rooms_price_rules')
                                            ->selectRaw('max(period)')
                                            ->whereRaw('room_id = rooms.id')
                                            ->whereRaw('period<='.$period)
                                            ->whereRaw("type = 'early_bird'")
                                            ->toSql();
        // Query to find the booking period discount based on the dates
        $booked_period_discount_query       = DB::table('rooms_price_rules')
                                            ->select('discount')
                                            ->whereRaw('room_id = rooms.id')
                                            ->where(function($query) use($period){
                                                $query->where(function($sub_query) use($period){
                                                    $sub_query->whereRaw('period >= '.$period)
                                                            ->whereRaw("type = 'last_min'")
                                                            ->whereRaw('period= min_price_rule_period');
                                                });
                                                $query->orWhere(function($sub_query) use($period){
                                                    $sub_query->whereRaw('period <= '.$period)
                                                            ->whereRaw("type = 'early_bird'")
                                                            ->whereRaw('period= max_price_rule_period');
                                                });
                                            })
                                            ->toSql();
        // Query to find the appropriate period for the length of stay based on total nights
        $length_of_stay_period_query        = DB::table('rooms_price_rules')
                                            ->selectRaw('max(period)')
                                            ->whereRaw('room_id = rooms.id')
                                            ->whereRaw('period<='.$total_nights)
                                            ->whereRaw("type = 'length_of_stay'")
                                            ->toSql();
        // Query to get the length of stay discount from price rules based on total nights
        $length_of_stay_discount_query      = DB::table('rooms_price_rules')
                                            ->select('discount')
                                            ->whereRaw('room_id = rooms.id')
                                            ->whereRaw('period<='.$total_nights)
                                            ->whereRaw("type = 'length_of_stay'")
                                            ->whereRaw("period = length_of_stay_period")
                                            ->toSql();
        // Create a rooms price details virtual table with all the possible prices applied
        $rooms_price_details_virtual_table  = DB::table('rooms')
                                            ->select('rooms.id as room_id')
                                            ->selectRaw("(".$calendar_price_total_query.") as calendar_total")
                                            ->selectRaw("(".$calendar_special_nights_query.") as special_nights")
                                            ->selectRaw("(".$calendar_special_weekends_query.") as special_weekends")

                                            ->selectRaw("(SELECT ".$total_weekends."-special_weekends) as normal_weekends")
                                            ->selectRaw("(SELECT ".$total_nights."-special_nights-normal_weekends) as normal_nights")
                                            ->selectRaw("(SELECT (rooms_price.night * normal_nights) + ( IF (rooms_price.weekend >0 , rooms_price.weekend , rooms_price.night) * normal_weekends)) as price_total")

                                            ->selectRaw("(SELECT IFNULL(price_total, 0)+ IFNULL(calendar_total, 0)) as base_total")
                                            
                                            ->selectRaw("(".$min_price_rule_period_query.") as min_price_rule_period")
                                            ->selectRaw("(".$max_price_rule_period_query.") as max_price_rule_period")
                                            ->selectRaw("(".$booked_period_discount_query.") as booked_period_discount")
                                            
                                            ->selectRaw("(".$length_of_stay_period_query.") as length_of_stay_period")
                                            ->selectRaw("(".$length_of_stay_discount_query.") as length_of_stay_discount")

                                            ->selectRaw("(SELECT Round(base_total*(booked_period_discount/100)) ) as booked_period_discount_price")
                                            ->selectRaw("(SELECT ROUND(base_total-IFNULL(booked_period_discount_price, 0))) as booked_period_base_total")

                                            ->selectRaw("(SELECT Round(booked_period_base_total*(length_of_stay_discount/100)) ) as length_of_stay_discount_price")
                                            ->selectRaw("(SELECT ROUND(booked_period_base_total - IFNULL(length_of_stay_discount_price, 0))) as discounted_base_total")
                                            
                                            ->selectRaw("(SELECT case when (".$total_guests."-rooms_price.guests) > 0 THEN (".$total_guests."-rooms_price.guests) else 0 end ) as extra_guests")

                                            ->selectRaw("(SELECT ROUND(IFNULL(discounted_base_total, 0) + rooms_price.cleaning ) ) as total")
                                            ->selectRaw("(SELECT ROUND(total/".$total_nights.")) as avg_price")
                                            ->selectRaw("(SELECT ROUND(total/".$total_nights.")) as night")
                                            ->selectRaw("( SELECT ROUND(((avg_price / currency.rate) * ".$currency_rate."))) as session_night")

                                            ->join('calendar','calendar.room_id','=','rooms.id', 'LEFT OUTER')
                                            ->join('rooms_price','rooms_price.room_id','=','rooms.id', 'LEFT')
                                            ->leftJoin('currency', 'currency.code','=', 'rooms_price.currency_code')
                                            ->groupBy('rooms.id')
                                            ->toSql();
        // Join the rooms price details virtual table with the rooms price
        $rooms          = $rooms->with([
                            'rooms_price' => function($query) use($rooms_price_details_virtual_table, $currency_rate, $min_price, $max_price, $max_price_check, $dates_available) {
                                $query->select('*');
                                if($dates_available) 
                                {
                                    $query->leftJoin(DB::raw("(".$rooms_price_details_virtual_table.") as rooms_price_details"), function($join) {
                                        $join->on('rooms_price.room_id','=','rooms_price_details.room_id');
                                    });
                                }
                                $query->with('currency');
                            },
                        ]);

        if($dates_available) {
            $rooms      = $rooms->leftJoin(DB::raw("(".$rooms_price_details_virtual_table.") as rooms_price_details"), function($join) {
                $join->on('rooms.id','=','rooms_price_details.room_id');
            });
            // Compare the session night price with the given min price and max price
            if($max_price_check >= MAXIMUM_AMOUNT) {
                $rooms->whereRaw('session_night >= '.$min_price);
            }
            else {
                $rooms->whereRaw('session_night >= '.$min_price.' and session_night <= '.$max_price);
            }
        }
        else {
            $rooms->whereHas('rooms_price', function($query)use( $currency_rate, $min_price, $max_price, $max_price_check){
                $query->join('currency', 'currency.code', '=', 'rooms_price.currency_code');
                if($max_price_check >= MAXIMUM_AMOUNT) {
                    $query->whereRaw('ROUND(((night / currency.rate) * '.$currency_rate.')) >= '.$min_price);
                }
                else {
                    $query->whereRaw('ROUND(((night / currency.rate) * '.$currency_rate.')) >= '.$min_price.' and ROUND(((night / currency.rate) * '.$currency_rate.')) <= '.$max_price);
                }
            });
        }
        // Price Filter End

        $bed_type = DB::table('bed_type')->select('id')->where('status','Active')->pluck('id');
        $rooms = $rooms->whereHas('rooms_beds',function($q) use ($bed_type,$beds){
            $q->whereIn('bed_id',$bed_type)
            ->havingRaw('SUM(count) >= ?', [$beds]);
        });

        $rooms = $rooms->orderByRaw('RAND(1234)')->paginate(18)->toJson();
        return response($rooms);
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
        while($sCurrentDate < $sEndDate) {
            $sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));  
            $aDays[]      = $sCurrentDate;  
        }
      
        return $aDays;  
    }

    /**
     * Get rooms photo details
     *
     * @param  array $request       Input values
     * @return json $rooms_photo    Rooms Photos Details
     */
    public function rooms_photos(Request $request)
    {            
        $rooms_id  = $request->rooms_id;
        $roomsDetails =  RoomsPhotos::where('room_id', $request->rooms_id)->get();

        return json_encode($roomsDetails);
    }

    /**
     * Get host experience photo details
     *
     * @param  array $request       Input values
     * @return json $host experience    host experience Details
     */
    public function host_experience_photos(Request $request)
    {            
        $rooms_id  = $request->rooms_id;
        $roomsDetails =  HostExperiencePhotos::where('host_experience_id', $request->rooms_id)->get();
        return json_encode($roomsDetails);
    }
}