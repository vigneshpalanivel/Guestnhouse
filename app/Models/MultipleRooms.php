<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;
use DB;
use Illuminate\Support\Facades\Route;
use JWTAuth;
use Auth;
use App\Models\RoomsStepsStatus;
use App\Models\MultipleRoomsStepStatus;
use App\Http\Start\Helpers;

class MultipleRooms extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'multiple_rooms';

    protected $appends = ['steps_count','photo_name','original_night', 'original_weekend','original_security','room_types','sub_room_type','original_symbol','action_url','checked_amenities','room_type_name','room_photo_name','code','multiple_room_image','multiple_room_images','multiple_rooms_length_of_stay','multiple_photo_name','original_cleaning','original_additional_guest','multiple_rooms_early_bird','multiple_rooms_last_min','multiple_rooms_availability','sub_checked_amenities','multiple_accommodates','multiple_beds','rooms_bed_type','selected_amenities','avaiable_rooms_count','discount_price'];

    public $timestamps = false;

    public function scopeCompleted($query){
        return $query->where('name', '!=', '')
                ->where('description', '!=', '')
                ->where('guests', '!=', '')->where('guests', '!=', 0)
                ->where('bedrooms', '!=', '')
                ->where('beds', '!=', '')
                ->where('bed_type', '!=', '')->where('bed_type', '!=', 0)
                ->where('bathrooms', '!=', '')
                ->where('extra_person', '!=', '')->where('extra_person', '!=', 0)
                ->where('extra_max_person', '!=', '')->where('extra_max_person', '!=', 0)
                ->where('night', '!=', '')->where('night', '!=', 0);
    }
    // Get steps_count using sum of rooms_steps_status
    public function getStepsCountAttribute()
    {
        $result = MultipleRoomsStepStatus::find($this->attributes['id']);

        return 5 - (@$result->basics + @$result->description + @$result->location + @$result->photos + @$result->pricing + @$result->calendar);
    }

    public function availability_rules() {
        return $this->hasMany('App\Models\MultipleRoomsAvailabilityRules', 'multiple_room_id', 'id');
    }

    public function setRoomTypeAttribute($input) {
        $room_type = RoomType::where('id', $input)->first();
        $is_shared = @$room_type->is_shared == 'Yes' ? 'Yes' : 'No';
        $this->attributes['room_type'] = $input;
        $this->attributes['is_shared'] = $is_shared;
    }

    public function getRoomsBedTypeAttribute() {
        $bed_type = RoomsBedType::where(['room_id'=> $this->attributes['id'],'type'=>'Multiple'])->get();

        return $bed_type;
    }

    // Get room_type_name from room_type table
    public function getRoomTypesAttribute()
    {
        return RoomType::all();
    }

    public function getAccommodatesAttribute(){
        if($this->attributes['accommodates']){
            return $this->attributes['accommodates'];
        }
        else{
            return '';
        }
    }

    public function getBedroomsAttribute(){
        if($this->attributes['bedrooms']){
            return $this->attributes['bedrooms'];
        }
        else{
            return '';
        }
    }

    public function getBathroomsAttribute(){
        if($this->attributes['bathrooms']){
            return $this->attributes['bathrooms'];
        }
        else{
            return '';
        }
    }

    public function getGuestsAttribute(){
        if($this->attributes['guests']){
            return $this->attributes['guests'];
        }
        else{
            return 1;
        }
    }


    public function getSelectedAmenitiesAttribute(){

        $multiple_room_id  = $this->attributes['id'];

        $amenities = Amenities::multiple_selected($multiple_room_id);

        return $amenities;
    }
    //selected_security_amenities
    public function getSelectedSecurityAmenitiesAttribute(){

        $multiple_room_id  = $this->attributes['id'];

        $amenities = Amenities::multi_selected_security($multiple_room_id);

        return $amenities;
    }



    // Get rooms featured photo_name URL
    public function getRoomPhotoNameAttribute()
    {
        $result = RoomsPhotos::where('room_id', $this->attributes['room_id']);

        if($result->count() == 0)
            return "room_default_no_photos.png";
        else
            return $result->first()->name;
    }

    public function isRoomCount($dates)
    {
        if(count($dates)>1){
            $calendar_room = [];
            foreach ($dates as $key => $value) {
                $room = Calendar::where(['room_id'=>$this->attributes['room_id'],'multiple_room_id'=>$this->attributes['id'],'date'=>$value,'status'=>'Available','source'=>'Calendar'])->first();
                
                if(!$room){
                   $calendar_room[$key] = $this->attributes['number_of_rooms'];
                }
                else{
                    return $calendar_room[$key] = $room->room_count;
                }

            }
            return max($calendar_room);
        }
        else if(count($dates)>0 && count($dates)<=1){
            $calendar_room = Calendar::where(['room_id'=>$this->attributes['room_id'],'multiple_room_id'=>$this->attributes['id'],'date'=>$dates[0],'status'=>'Available','source'=>'Calendar'])->first();
            if(!$calendar_room){
                return $this->attributes['number_of_rooms'];
            }
            else{
                return $calendar_room->room_count;
            }
        }
        else{
            return $this->attributes['number_of_rooms'];
        }
      
    }

    public function getMultipleRoomTypeAttribute()
    {
        return $this->attributes['room_type'];
     
    }

    public function getActionUrlAttribute()
    {
        return url('/').'/payments/book/'.$this->attributes['id'];
    }
    // Join with currency table
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency','currency_code','code');
    }
    // Join with currency table
    public function calendar()
    {
        
        return $this->hasMany('App\Models\Calendar','multiple_room_id','id');
    }
     // Join with currency table
    public function room_type()
    {
        return $this->belongsTo('App\Models\RoomType','id','room_type');
    }

    // Join with room_type_data table
    public function room_type_data()
    {
        return $this->belongsTo('App\Models\RoomType','room_type','id');
    }
     // Join with reviews table

    public function reviews()
    {
        return $this->hasMany('App\Models\Reviews','room_id','id')->where('user_to', $this->attributes['user_id']);
    }
    public function multiple_rooms_details()
    {
        return $this->hasMany('App\Models\MultipleRoomImages','multiple_room_id','id');
    }

    public function multiple_rooms_images()
    {
        return $this->hasMany('App\Models\MultipleRoomImages','multiple_room_id','id');
    }
    public function getMultipleRoomImageAttribute(){
      return MultipleRoomImages::where('multiple_room_id',$this->attributes['id'])->orderBy('featured')->get();
    }

    public function getMultipleRoomImagesAttribute(){
      return MultipleRoomImages::where('multiple_room_id',$this->attributes['id'])->get();
    }

    public function getOriginalNightAttribute(){
        return $this->attributes['night'];
    }

    public function getOriginalSecurityAttribute(){
        return $this->attributes['security'];
    }

    // Get actual result of cleaning price
    public function getOriginalCleaningAttribute()
    {
        return $this->attributes['cleaning'];
    }

    // Get actual result of additional_guest price
    public function getOriginalAdditionalGuestAttribute()
    {
        return $this->attributes['additional_guest'];
    }

    // Get result of cleaning price for current currency
    public function getCleaningAttribute()
    {
        return $this->currency_calc('cleaning');
    }

    // Get result of cleaning price for current currency
    public function getSecurityAttribute()
    {
        return $this->currency_calc('security');
    }

    // Get result of additional_guest price for current currency
    public function getAdditionalGuestAttribute()
    {
        return $this->currency_calc('additional_guest');
    }

    public function price_rules() {
        return $this->hasMany('App\Models\MultipleRoomsPriceRules', 'multiple_room_id', 'id');
    }

    public function length_of_stay_rules() {
       return $this->price_rules()->type('length_of_stay');
    }

    public function early_bird_rules() {
        return $this->price_rules()->type('early_bird');
    }

    public function last_min_rules() {
        return $this->price_rules()->type('last_min');
    }


    public function getSubRoomTypeAttribute(){
        return $this->attributes['room_type'];
    }

    public function getMultipleRoomsLengthOfStayAttribute(){
        
        return MultipleRoomsPriceRules::where(['multiple_room_id'=>$this->attributes['id'],'type'=>'length_of_stay'])->get();
    }

    public function getMultipleRoomsEarlyBirdAttribute(){
        
        return MultipleRoomsPriceRules::where(['multiple_room_id'=>$this->attributes['id'],'type'=>'early_bird'])->get();
    }

    public function getMultipleRoomsLastMinAttribute(){
        
        return MultipleRoomsPriceRules::where(['multiple_room_id'=>$this->attributes['id'],'type'=>'last_min'])->get();
    }

    public function getMultipleRoomsAvailabilityAttribute(){
        
        return MultipleRoomsAvailabilityRules::where(['multiple_room_id'=>$this->attributes['id']])->get();
    }

    public function getOriginalWeekendAttribute(){
        return $this->attributes['weekend'];
    }
    
    // Get currenct record symbol
    public function getOriginalSymbolAttribute()
    {
        // dd($this->attributes['currency_code']);
       $symbol = DB::table('currency')->where('code', $this->attributes['currency_code'])->first()->symbol;
        return $symbol;
    }
    // Get room_type_name from room_type table
    public function getRoomTypeNameAttribute()
    {
        if($this->attributes['room_type']){
            return @RoomType::find($this->attributes['room_type'])->name;
        }
        return '';
    }
    // Get result of night price for current currency
    public function getNightAttribute()
    {
        return $this->currency_calc('night');
    }

    // Join with rooms table
    public function rooms()
    {
        return $this->belongsTo('App\Models\Rooms','room_id','id');
    }

    // Join with multiple_rooms_image table
    public function rooms_photos()
    {
        return $this->hasMany('App\Models\MultipleRoomImages','multiple_room_id','id');
      
    }
    // Join with rooms_price table
    public function rooms_price()
    {
        return $this->belongsTo('App\Models\RoomsPrice','room_id','room_id');
        
    }
    // Join with rooms_address table
    public function rooms_address()
    {
        return $this->belongsTo('App\Models\RoomsAddress','room_id','room_id');
    }
     // Join with users table
    public function users()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function getCheckedAmenitiesAttribute(){

        $amenities = explode(',', $this->attributes['amenities']);

        return $amenities;
    }

    public function getMultipleAccommodatesAttribute(){

        if(@$this->attributes['accommodates'] && @$this->attributes['number_of_rooms']){
            @$accommodates = @$this->attributes['accommodates'] * @$this->attributes['number_of_rooms'];

            return @$accommodates;
        }
        return 0;
    }
    //multiple_rooms_accommodates
    public function getMultipleRoomsAccommodatesAttribute(){

        if(@$this->attributes['accommodates'] && @$this->attributes['number_of_rooms']){
            @$accommodates = @$this->attributes['accommodates'] * $this->avaiable_rooms_count;

            return @$accommodates;
        }
        return 0;
    }
    //avaiable_rooms_count
    public function getAvaiableRoomsCountAttribute(){
        if(request()->checkin) {
            $helper = new Helpers;
            $checkin  = $this->get_date_format(request()->checkin);
            $checkout = $this->get_date_format(request()->checkout);
        }
        $get_room_count = 0;
        if(request()->checkin){
            $get_room_count = $this->calendar()->whereBetween('date',[$checkin,$checkout])->get()->max('room_count');
        }
        return $this->attributes['number_of_rooms']-$get_room_count;
            
    }
     //discount_price
    public function getDiscountPriceAttribute(){
       if(request()->checkin) {
            $checkin  = $this->get_date_format(request()->checkin);
            $checkout = $this->get_date_format(request()->checkout);
            $total_guests = request()->guest+request()->children;
        }
        $get_room_count = 0;
        if(request()->checkin){
            return $this->length_of_stay_calculate($checkin,$checkout,$total_guests,$this->attributes['id']);
        }
        return $this->night;
    }
    // get_single_bed_type
    public function getGetSingleBedTypeAttribute()
    {
        $return = [];
        $val = BedType::where('status','Active')->limit(4)->get();
        foreach ($val as $key => $value) {
            $val[$key]->count = 0;
            $url_array = array('id' => $value->id, 'name'=> $value->name,'count'=> 0,'icon'=> $value->icon); 
            $return[]= $url_array;
        }
        return $return;
    }
     // get_first_bed_type
    public function getGetFirstBedTypeAttribute()
    {
        $bed_types       = DB::table('bed_type')->where('status','Active')->select('id')->get()->pluck('id');
        // bed_room_no
        $bed_room_count = $this->attributes['bedrooms'];
        $return=[];
        if($bed_room_count > 0){ 
            for ($i=1; $i < $bed_room_count+1; $i++) { 
                $return[$i] = [];
                $get = RoomsBeds::where('room_id',$this->attributes['id'])->where('bed_room_no',$i)->whereIn('bed_id',$bed_types)->get();

                if($get->count() > 0){ 
                    foreach ($get as $key => $value) {
                        $val = BedType::where('id',$value->bed_id)->where('status','Active')->first();
                        if($val->count()){
                            $url_array = array('id' => $val->id, 'name'=> $val->name,'count'=> $value->count,'icon'=> $val->icon);
                            $return[$i][]= $url_array;
                        }
                    }
                }
                else{
                    $val = BedType::where('status','Active')->limit(4)->get();
                    foreach ($val as $key => $abc) {
                        $val[$key]->count = 0;
                        $url_array =array('id' => $abc->id, 'name'=> $abc->name,'count'=>0,'icon'=> $abc->icon); 
                        $return[$i][]=@$url_array;
                    }
                }
            }
            return @$return;

        }
        else
        {
            $bed_room_count = $bed_room_count>0?$bed_room_count:1;
            $val = BedType::where('status','Active')->limit(4)->get();
            for ($i=1; $i < $bed_room_count+1; $i++) { 
                $return[$i] = [];
                foreach ($val as $key => $value) {
                    $val[$key]->count = 0;
                    $url_array =array('id' => $value->id, 'name'=> $value->name,'count'=>0,'icon'=> $value->icon);
                    $return[$i][]= $url_array;
                }
            }
        }

        return @$return;
    }
    // get_common_bed_type
    public function getGetCommonBedTypeAttribute()
    {
        $return = [];
        $bed_types       = DB::table('bed_type')->where('status','Active')->select('id')->get()->pluck('id');
        $get = RoomsBeds::where('room_id',$this->attributes['id'])->where('bed_room_no','common')->whereIn('bed_id',$bed_types)->get();

        if($get->count() > 0){ 
            foreach ($get as $key => $value) {
                $val = BedType::where('id',@$value->bed_id)->where('status', 'Active')->first();
                $url_array =array('id' => @$val->id, 'name'=>@$val->name,'count'=>@$value->count,'icon'=>@$val->icon); 
                $return[]= $url_array;
            }
        }
        else{
            $val = BedType::where('status','Active')->limit(4)->get();
            foreach ($val as $key => $abc) {
                $url_array =array('id' => @$abc->id, 'name'=>@$abc->name,'count'=>0,'icon'=>@$abc->icon); 
                $return[]= $url_array;
            }
        }
        return $return;
    }

     // get_bathrooms
    public function getGetBathroomsAttribute()
    {
        // bed_room_no
        $bed_room_count = $this->attributes['bedrooms'];
        $return = [];
        if ($bed_room_count > 0) {
            for ($i = 1; $i < $bed_room_count + 1; $i++) {
                $get = RoomsBeds::where('room_id', $this->attributes['id'])->where('bed_room_no', $i)->get();

                if ($get->count() > 0) {
                    foreach ($get as $key => $value) {
                        $val = BedType::where('id', $value->bed_id)->first();
                        if($val){
                            if ($val->count()) {
                                $url_array = array('bathrooms' => @$batrooms_value);
                                $return[@$i][@$val->id] = @$url_array;
                            }
                        }
                    }
                }
                else {
                    $val = BedType::where('status', 'Active')->limit(4)->get();
                    foreach ($val as $key => $abc) {
                        $val[$key]->count = 0;
                        $url_array = array('bathrooms' => @$batrooms_value);
                        $return[@$i][@$abc->id] = @$url_array;
                    }
                }
            }
            return $return;

        }
        else {
            $bed_room_count = $bed_room_count > 0 ? $bed_room_count : 1;
            $val = BedType::where('status', 'Active')->limit(4)->get();
            $batrooms_value = 'No';
            for ($i = 1; $i < $bed_room_count + 1; $i++) {
                foreach ($val as $key => $value) {
                    $val[$key]->count = 0;
                    $url_array = array('bathrooms' => $batrooms_value);
                    $return[$i][$value->id] = $url_array;
                }
            }
        }

        return $return;
    }

    // get_common_bathrooms
    public function getGetCommonBathroomsAttribute()
    {
        $get = RoomsBeds::where('room_id', $this->attributes['id'])->where('bed_room_no', 'common')->get();
        
        if ($get->count() > 0) {
            foreach ($get as $key => $value) {
                $val = BedType::where('id', @$value->bed_id)->first();
                if ($val != '') {
                    $url_array = array('bathrooms' => @$batrooms_value);
                    $return[@$val->id] = @$url_array;
                }
            }
        }
        else {
            $val = BedType::where('status', 'Active')->limit(4)->get();
            foreach ($val as $key => $abc) {
                $url_array = array('bathrooms' => @$batrooms_value);
                $return[$abc->id] = $url_array;
            }
        }
        return $return;
    }
    

    public function getMultipleBedsAttribute(){

        if(@$this->attributes['beds'] && @$this->attributes['number_of_rooms']){
            @$beds = @$this->attributes['beds'] * @$this->attributes['number_of_rooms'];

            return @$beds;
        }
        return 0;
    }

    public function getNumberOfRoomsAttribute(){

        return (int)$this->attributes['number_of_rooms'];
    }

    public function getSubCheckedAmenitiesAttribute(){
        $checked_amenities = [];
        if($this->attributes['amenities']){
            $amenities = explode(',', $this->attributes['amenities']);
            if(count($amenities)>0){
                foreach ($amenities as $key => $value) {
                    $checked_amenities[$key] = Amenities::find($value);
                }
            }
        }

        return $checked_amenities;
    }

     // Get result of calendar notes for given date
    public function spots_left($date)
    {
        $where = ['multiple_room_id' => $this->attributes['id'], 'date' => $date];

        $result = Calendar::where($where);


        if($result->count())
            return $result->first()->spots_booked > 0 && $result->first()->is_shared == 'Yes' ? $result->first()->spots_left : false;
        else
            return false;
    }

    // Get rooms featured photo_name URL
    public function getMultiplePhotoNameAttribute()
    {
        $result = MultipleRoomImages::where('multiple_room_id', $this->attributes['id'])->where('featured','Yes');

        if($result->count() == 0)
            $url = url('/images/room_default_no_photos.png');
        else
            $url = url('/images/multiple_rooms/'.$this->attributes['id'].'/'.$result->first()->name);

        return $url;
    }

     // Get rooms featured photo_name URL
    public function getBannerPhotoNameAttribute()
    {
        $result = MultipleRoomImages::where('multiple_room_id', $this->attributes['id']);

        if($result->count() == 0)
            return "room_default_no_photos.png";
        else
            return "rooms/".$this->attributes['id']."/".$result->first()->banner_image_name;
    }

    // Get rooms featured photo_name URL
    public function getPhotoNameAttribute()
    {
        $result = MultipleRoomImages::where('room_id', $this->attributes['room_id'])->where('multiple_room_id', $this->attributes['id'])->where('featured','Yes');
        $result1 = MultipleRoomImages::where('room_id', $this->attributes['room_id'])->where('multiple_room_id', $this->attributes['id']);

        if($result->count() == 0)
        {
            if($result1->count() == 0)
                return "room_default_no_photos.png";
            else
            {
                $photo_details = pathinfo($result1->first()->name); 
                $name = @$photo_details['filename'].'_450x250.'.@$photo_details['extension'];
                return "/images/multiple_rooms/".$this->attributes['id']."/".$name;
            }


        }
        else
        {
           $photo_details = pathinfo($result->first()->name); 
        $name = @$photo_details['filename'].'_450x250.'.@$photo_details['extension'];
            return "/images/multiple_rooms/".$this->attributes['id']."/".$name;
        }
    }
    

    public function getWeekendAttribute()
    {
        return $this->currency_calc('weekend');
    }

    public function getExtraPersonAttribute()
    {
        return $this->currency_calc('extra_person');
    }

    // Check rooms table user_id is equal to current logged in user id
    public static function check_user($id)
    {
        return MultipleRooms::where(['id' => $id, 'user_id' => Auth::user()->id])->first();
    }
    // Get result of night price for given date
    public function price($date,$multi_room_id,$type)
    {
        
        $where = ['multiple_room_id' =>$multi_room_id, 'date' => $date];

        $result = Calendar::where($where);
        if($result->count())
            return $result->first()->price;
        else
            { 
             if((date('N',strtotime($date))==5 || date('N',strtotime($date))==6) && $this->attributes['weekend'] !=0)
                return $this->attributes['weekend'];
            else
            return $this->attributes['night'];//return $this->currency_calc('night');
            }
    }

    // Get result of night price for given date
    public function price1($date) {
        $where = ['multiple_room_id' => $this->attributes['id'], 'date' => $date];

        $result = Calendar::where($where);

        if ($result->count()) {
            return $result->first()->price;
        } else {
            if ((date('N', strtotime($date)) == 5 || date('N', strtotime($date)) == 6) && $this->attributes['weekend'] != 0) {
                return $this->attributes['weekend'];
            } else {
                return $this->attributes['night'];
            }
//return $this->currency_calc('night');
        }
    }

    // Get result of roomscount for given date
    public function roomscount($date,$multi_room_id,$type)
    {
        
        $where = ['multiple_room_id' =>$multi_room_id, 'date' => $date,'source'=>'Reservation'];

        $result = Calendar::where($where);

        $where1 = ['multiple_room_id' =>$multi_room_id, 'date' => $date,'source'=>'Calendar'];

        $result1 = Calendar::where($where1);

        if($result->count()){
            if($result1->count()){
                $room_count =  $result1->first()->room_count-$result->first()->room_count;
            }
            else{
                $room_count = $this->attributes['number_of_rooms']-$result->first()->room_count;    
            }
            
        }
        else{
            if($result1->count()){
                $room_count = $result1->first()->room_count;
            }
            else{
                $room_count = $this->attributes['number_of_rooms'];    
            }
            
        }

        if($room_count>=0){
            return $room_count;
        }
        else
            return 0;
    }

    // Get result of calendar event status for given date
    public function status($date)
    {
        $where = ['multiple_room_id' => $this->attributes['id'], 'date' => $date];
        

        $result = Calendar::where($where);

        if($result->count())
            return $result->first()->status;
        else
            return false;
    }
    
    // Get result of calendar notes for given date
    public function notes($date, $multi_room_id = 0)
    {
        $where = ['room_id' => $this->attributes['room_id'], 'multiple_room_id' => $this->attributes['id'], 'date' => $date];
        
        
        $result = Calendar::where($where);

        if($result->count())
            return $result->first()->notes;
        else
            return '';
    }
       // Calculation for current currency conversion of given price field
    public function currency_calc($field)
    {  //get currenct url
      /*$route=@Route::getCurrentRoute();
      
      if($route)
      {
        $api_url = @$route->getPath();
      }
      else
      {
        $api_url = '';
      }
          $url_array=explode('/',$api_url);
            //Api currency conversion
          if(@$url_array['0']=='api')*/
        if(request()->segment(1) == 'api')
          { 
            $user_details = JWTAuth::parseToken()->authenticate(); 
            
            $rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

            $usd_amount = $this->attributes[$field] / $rate;

            $api_currency = $user_details->currency_code; 

            $default_currency = Currency::where('default_currency',1)->first()->code;

            $session_rate = Currency::whereCode($user_details->currency_code!=null?$user_details->currency_code :$default_currency)->first()->rate;

               return round($usd_amount * $session_rate);

         }
         else
         {
        $rate = Currency::whereCode($this->attributes['currency_code'])->first()->rate;

        $usd_amount = $this->attributes[$field] / $rate;

        $default_currency = Currency::where('default_currency',1)->first()->code;

        $session_rate = Currency::whereCode((Session::get('currency')) ? Session::get('currency') : $default_currency)->first()->rate;

        return round($usd_amount * $session_rate);
        }
    }

    // Get default currency code if session is not set
    public function getCodeAttribute()
    {
        //get currenct url
      /*$route=@Route::getCurrentRoute();
      
      if($route)
      {
        $api_url = @$route->getPath();
      }
      else
      {
        $api_url = '';
      }
          $url_array=explode('/',$api_url);
            //Check current user login is web or mobile
          if(@$url_array['0']=='api')*/
        if(request()->segment(1) == 'api')
          { 
    
            if(JWTAuth::parseToken()->authenticate()->currency_code) 
             //set user currency code 
             return JWTAuth::parseToken()->authenticate()->currency_code;
             
            else
                //set default currency  code . for user currency code not given.
             return DB::table('currency')->where('default_currency', 1)->first()->code;
          }
          else
          {
            if(Session::get('currency'))
           return Session::get('currency');
           else
           return DB::table('currency')->where('default_currency', 1)->first()->code;

          }
       
    }


    public static function getEmptyRoom(){
      return array(
          'id' => '', 
          'description' => '', 
          'bedrooms' => null, 
          'beds' => null, 
          'bathrooms' => null, 
          'bed_type' => null, 
          'guests' => null, 
          'original_night' => '', 
          'upload_multiple_rooms_images' => array(), 
        );
    }

    public static function get_days($sStartDate, $sEndDate)
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

    public static function get_date_format($date)
    {      
        $helper = new Helpers;
        return date('Y-m-d', $helper->custom_strtotime($date));
    }

    public function length_of_stay_calculate($checkin,$checkout,$total_guests,$multiple_room_id){
         // public function multiple_rooms($dates,$period,$total_nights,$total_weekends,$total_guests,$children,$currency_rate){
        $currency_rate = Currency::whereCode((Session::get('currency')) ? Session::get('currency') : $default_currency)->first()->rate;
        $from                               = new \DateTime($checkin);
        $today                              = new \DateTime(date('Y-m-d'));
        $period                             = $from->diff($today)->format("%a")+1;
        $days                               = $this->get_days($checkin, $checkout);
        unset($days[count($days)-1]);
        $total_nights                       = count($days);
        $dates                              = implode(',', $days);
        $total_weekends = 0;
        foreach($days as $day) {
            $weekday = date('N', strtotime($day));
            if( in_array($weekday, [5,6]) ) {
                $total_weekends++;
            }
        }


            $calendar_price_total_query         = DB::table("calendar")
                                                ->selectRaw('sum(price)')
                                                ->whereRaw('calendar.multiple_room_id = "'.$multiple_room_id.'"')
                                                ->whereRaw('FIND_IN_SET(calendar.date, "'.$dates.'")')
                                                ->toSql();

            // Query to count the total calendar result for rooms as special nights
            $calendar_special_nights_query      = DB::table("calendar")
                                                ->selectRaw("count('*')")
                                                ->whereRaw('calendar.multiple_room_id = "'.$multiple_room_id.'"')
                                                ->whereRaw('FIND_IN_SET(calendar.date, "'.$dates.'")')
                                                ->toSql();
            // Query to count the total weekend calendar result for rooms as special weekends
            $calendar_special_weekends_query    = DB::table("calendar")
                                                ->selectRaw("count('*')")
                                                ->whereRaw('calendar.multiple_room_id = "'.$multiple_room_id.'"')
                                                ->whereRaw('FIND_IN_SET(calendar.date, "'.$dates.'")')
                                                ->whereRaw('( WEEKDAY(date) = 4 OR WEEKDAY(date) = 5 )')
                                                ->toSql();

            // discount price start

             // Query to get rooms price rules minimum period for last min booking
            $min_price_rule_period_query        = DB::table('multiple_rooms_price_rules')
                                                ->selectRaw('min(period)')
                                                ->whereRaw('multiple_room_id = "'.$multiple_room_id.'"')
                                                ->whereRaw('period>='.$period)
                                                ->whereRaw("type = 'last_min'")
                                                ->toSql();
            // Query to get rooms price rules maximum period for early bird booking
            $max_price_rule_period_query        = DB::table('multiple_rooms_price_rules')
                                                ->selectRaw('max(period)')
                                                ->whereRaw('multiple_room_id = "'.$multiple_room_id.'"')
                                                ->whereRaw('period<='.$period)
                                                ->whereRaw("type = 'early_bird'")
                                                ->toSql();
            // Query to find the booking period discount based on the dates
            $booked_period_discount_query       = DB::table('multiple_rooms_price_rules')
                                                ->select('discount')
                                                ->whereRaw('multiple_room_id = "'.$multiple_room_id.'"')
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
            $length_of_stay_period_query        = DB::table('multiple_rooms_price_rules')
                                                ->selectRaw('max(period)')
                                                ->whereRaw('multiple_room_id = "'.$multiple_room_id.'"')
                                                ->whereRaw('period<='.$total_nights)
                                                ->whereRaw("type = 'length_of_stay'")
                                                ->toSql();
            // Query to get the length of stay discount from price rules based on total nights
            $length_of_stay_discount_query      = DB::table('multiple_rooms_price_rules')
                                                ->select('discount')
                                                ->whereRaw('multiple_room_id = "'.$multiple_room_id.'"')
                                                ->whereRaw('period<='.$total_nights)
                                                ->whereRaw("type = 'length_of_stay'")
                                                ->whereRaw("period = length_of_stay_period")
                                                ->toSql(); 

            // discount price end

            
            // // Create a rooms price details virtual table with all the possible prices applied
            $rooms_price_details_virtual_table  = DB::table('multiple_rooms')
                                                ->select('multiple_rooms.room_id as room_id')
                                                ->selectRaw("(".$calendar_price_total_query.") as calendar_total")
                                                ->selectRaw("(".$calendar_special_nights_query.") as special_nights")
                                                ->selectRaw("(".$calendar_special_weekends_query.") as special_weekends")

                                                ->selectRaw("(SELECT ".$total_weekends."-special_weekends) as normal_weekends")
                                                ->selectRaw("(SELECT ".$total_nights."-special_nights-normal_weekends) as normal_nights")
                                                ->selectRaw("(SELECT (multiple_rooms.night * normal_nights) + ( IF (multiple_rooms.weekend >0 , multiple_rooms.weekend , multiple_rooms.night) * normal_weekends)) as price_total")

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
                                                

                                                ->selectRaw("(SELECT ROUND(IFNULL(discounted_base_total, 0) + multiple_rooms.cleaning + multiple_rooms.security ) ) as total")




                                                 // ->selectRaw("(SELECT ROUND(IFNULL(base_total, 0) + multiple_rooms.cleaning + multiple_rooms.security ) ) as total")
                                            /* ->selectRaw("(SELECT ROUND(IFNULL(discounted_base_total, 0) + rooms_price.cleaning + rooms_price.security + (extra_guests * rooms_price.additional_guest) ) ) as total") */
                                           



                                           

                                                ->selectRaw("(SELECT ROUND(total/".$total_nights.")) as avg_price")
                                                ->selectRaw("(SELECT ROUND(total/".$total_nights.")) as night")
                                                ->selectRaw("( SELECT ROUND(((avg_price / currency.rate) * ".$currency_rate."))) as session_night")
                                                ->whereRaw('multiple_rooms.id = "'.$multiple_room_id.'"')
                                                ->join('calendar','calendar.multiple_room_id','=','multiple_rooms.id', 'LEFT OUTER')
                                                ->leftJoin('currency', 'currency.code','=', 'multiple_rooms.currency_code')
                                                ->groupBy('multiple_rooms.room_id')
                                                ->first();
            return $rooms_price_details_virtual_table->session_night;


    }
   
}
