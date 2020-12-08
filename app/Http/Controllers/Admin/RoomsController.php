<?php

/**
 * Rooms Controller
 *
 * @package     Makent
 * @subpackage  Controller
 * @category    Rooms
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DataTables\RoomsDataTable;
use App\Models\BedType;
use App\Models\PropertyType;
use App\Models\RoomType;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Amenities;
use App\Models\AmenitiesType;
use App\Models\RoomsPhotos;
use App\Models\Rooms;
use App\Models\User;
use App\Models\RoomsAddress;
use App\Models\RoomsDescription;
use App\Models\RoomsDescriptionLang;
use App\Models\RoomsPrice;
use App\Models\RoomsStepsStatus;
use App\Models\Reservation;
use App\Models\SavedWishlists;
use App\Models\SpecialOffer;
use App\Models\Reviews;
use App\Models\Payouts;
use App\Models\HostPenalty;
use App\Models\ImportedIcal;
use App\Models\Calendar;
use App\Models\Messages;
use App\Models\PayoutPreferences;
use App\Models\RoomsBeds;
use App\Http\Helper\PaymentHelper;
use App\Http\Start\Helpers;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EmailController;
use Validator;
use App\Models\RoomsPriceRules;
use App\Models\RoomsAvailabilityRules;
use App\Models\MultipleRooms;
use App\Models\MultipleRoomsStepStatus;
use App\Models\MultipleRoomImages;
use App\Models\MultipleRoomsPriceRules;
use App\Models\MultipleRoomsAvailabilityRules;
use App\Models\MultipleReservation;
use App\Models\MultipleSpecialOffer;
use App\Models\RoomsBedType;

use Session;
use DB;

class RoomsController extends Controller
{


    protected $payment_helper; // Global variable for Helpers instance   

    protected $helper;  // Global variable for instance of Helpers

    public function __construct(PaymentHelper $payment)
    {
        $this->payment_helper = $payment;        
        $this->helper = new Helpers;
    }

    /**
     * Load Datatable for Rooms
     *
     * @param array $dataTable  Instance of RoomsDataTable
     * @return datatable
     */
    public function index(RoomsDataTable $dataTable)
    {
        return $dataTable->render('admin.rooms.view');
    }

    /**
     * Add a New Room
     *
     * @param array $request  Input values
     * @return redirect     to Rooms view
     */
    public function add(Request $request){
        if(!$_POST){
            $bedrooms = [];
            $bedrooms[0] = 'Studio';
            for($i=1; $i<=10; $i++)
                $bedrooms[$i] = $i;

            $beds = [];
            for($i=1; $i<=16; $i++)
                $beds[$i] = ($i == 16) ? $i.'+' : $i;

            $bathrooms = [];
            $bathrooms[0] = 0;
            for($i=0.5; $i<=8; $i+=0.5)
                $bathrooms["$i"] = ($i == 8) ? $i.'+' : $i;
            
            $bathrooms1 = [];
            for($i=0; $i<=8; $i++)
                $bathrooms1["$i"] = ($i == 8) ? $i.'+' : $i;


            $accommodates = [];
            for($i=1; $i<=16; $i++)
                $accommodates[$i] = ($i == 16) ? $i.'+' : $i;

            $data['bedrooms']      = $bedrooms;
            $data['bathrooms1']    = $bathrooms1;
            $data['beds']          = array_values($beds);
           $data['bed_type']      = BedType::where('status','Active')->pluck('name','id');
            $data['bed_types']     = BedType::active_all();

            // $data['bed_type']      = BedType::where('status','Active')->pluck('name','id');
            $data['bathrooms']     = $bathrooms;
            $data['property_type'] = PropertyType::where('status','Active')->pluck('name','id');
            $data['room_type']     = RoomType::where('status','Active')->pluck('name','id');
            $data['room_type_multiple']     = RoomType::where(['status'=>'Active'])->pluck('name','id');
            $data['accommodates']  = $accommodates;
            $data['country']       = Country::pluck('long_name','short_name');
            $data['amenities']     = Amenities::active_all();
            $data['amenities_type'] = AmenitiesType::active_all();
            $data['users_list']    = User::select('id',DB::raw('CONCAT(id," - ",first_name) AS first_name'))->whereStatus('Active')->pluck('first_name','id');
             $data['length_of_stay_options'] = Rooms::getLenghtOfStayOptions();
            $data['availability_rules_months_options'] = Rooms::getAvailabilityRulesMonthsOptions();
            $singlebedtype = BedType::where('status', 'Active')->limit(4)->get();
            $single_bed_type = [];
            foreach ($singlebedtype as $key => $value) {
                $singlebedtype[$key]->count = 0;
                $url_array = array('id' => @$value->id, 'name' => @$value->name, 'count' => 0, 'icon' => @$value->icon);
                $single_bed_type[] = @$url_array;
            }

            $firstbedtype = BedType::where('status', 'Active')->limit(4)->get();
            $first_bed_type = [];
            foreach ($firstbedtype as $key1 => $value1) {
                $firstbedtype[$key1]->count = 0;
                $url_array1 = array('id' => @$value1->id, 'name' => @$value1->name, 'count' => 0, 'icon' => @$value1->icon);
                $first_bed_type[1][] = @$url_array1;
            }

            $bathrooms = BedType::where('status', 'Active')->limit(4)->get();
            $bath_rooms = [];
            foreach ($bathrooms as $key => $abc) {
                $bathrooms[$key]->count = 0;
                $url_array = array('bathrooms' => @$batrooms_value);
                $bath_rooms[0][@$abc->id] = @$url_array;
            }

            $commonbedtype = BedType::where('status', 'Active')->limit(4)->get();
            $common_bed_type = [];
            foreach ($commonbedtype as $key => $abc) {
                $url_array = array('id' => @$abc->id, 'name' => @$abc->name, 'count' => 0, 'icon' => @$abc->icon, 'bathrooms' => @$batrooms_value);
                $common_bed_type[] = @$url_array;
            }

            // dd($first_bed_type);

            $data['get_single_bed_type'] = $single_bed_type;
            $data['first_bed_type1'] = $first_bed_type;
            //dd($data['first_bed_type1']);
            $data['get_common_bed_type'] = $common_bed_type;
            $data['first_bed_type'] = BedType::where('status', 'Active')->limit(4)->get();

            $data['get_bathrooms'] = $bath_rooms;
            $data['get_common_bathrooms'] = $bath_rooms;
            $data['firstbedtypeid'] = BedType::where('status', 'Active')->first()->id;

            $data['length_of_stay_options'] = Rooms::getLenghtOfStayOptions();
            $data['availability_rules_months_options'] = Rooms::getAvailabilityRulesMonthsOptions();

            return view('admin.rooms.add', $data);
        }
        else if($_POST){
          
            \DB::beginTransaction(); 
            try{
            $photos_uploaded = array();
            if(UPLOAD_DRIVER=='cloudinary'){
                if(isset($_FILES["photos"]["name"])){
                    foreach($_FILES["photos"]["error"] as $key=>$error) {
                        $tmp_name = $_FILES["photos"]["tmp_name"][$key];
                        $name = str_replace(' ', '_', $_FILES["photos"]["name"][$key]);
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $name = time().$key.'_.'.$ext;
                    if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif'){     $c=$this->helper->cloud_upload($tmp_name);
                            if($c['status']!="error") {
                                $name=$c['message']['public_id'];    
                            }
                            else {
                                flash_message('danger', $c['message']); // Call flash message function
                                return redirect(ADMIN_URL.'/rooms');
                            }
                            $photos_uploaded[] = $name;
                        }
                    }
                }
            }

            $rooms = new Rooms;

            $rooms->user_id       = $request->user_id;
            $rooms->calendar_type = 'Always';
            $rooms->bedrooms      = $request->bedrooms;
            $rooms->bathrooms     = $request->bathrooms;
            $rooms->bathroom_shared = $request->bathroom_shared;
            $rooms->property_type = $request->property_type;
            $rooms->room_type     = $request->room_type;
            $rooms->type           = $request->type;
            $rooms->accommodates  = $request->accommodates;
            $rooms->name          = $request->name[0];

            $search     = '#(.*?)(?:href="https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch?.*?v=))([\w\-]{10,12}).*#x';
            $count      = preg_match($search, $request->video);
            if($count == 1) {
                $replace    = 'https://www.youtube.com/embed/$2';
                $video      = preg_replace($search,$replace,$request->video);
                $rooms->video = $video;
            }
            else {
                $rooms->video = $request->video;
            }

             $rooms->sub_name      = $request->type == 'Single' ? RoomType::find($request->room_type)->name.' in '.$request->city : $request->city;

            $rooms->summary       = $request->summary[0];
            $rooms->amenities     = @implode(',', @$request->amenities);
            $rooms->booking_type  = $request->booking_type;
            $rooms->started       = 'Yes';
            $rooms->status        = 'Listed';
            $rooms->verified      = 'Approved';
            $rooms->cancel_policy = $request->cancel_policy;

            $rooms->save();

            $rooms_address = new RoomsAddress;

            $latt=$request->latitude;
            $longg=$request->longitude;
            if($latt==''||$longg==''){
                $address        =$request->address_line_1.' '.$request->address_line_2.' '.$request->city.' '.$request->state.' '.$request->country;
                $latlong=$this->latlong($address);

                $latt       = $latlong['lat'];
                $longg      = $latlong['long'];
            }
            
            $rooms_address->room_id        = $rooms->id;
            $rooms_address->address_line_1 = $request->address_line_1;
            $rooms_address->address_line_2 = $request->address_line_2;
            $rooms_address->city           = $request->city;
            $rooms_address->state          = $request->state;
            $rooms_address->country        = $request->country;
            $rooms_address->postal_code    = $request->postal_code;
            $rooms_address->latitude       = $latt;
            $rooms_address->longitude      = $longg;

            $rooms_address->save();

            $rooms_description = new RoomsDescription;
            $rooms_description->room_id               = $rooms->id;
            $rooms_description->space                 = $request->space[0];
            $rooms_description->access                = $request->access[0];
            $rooms_description->interaction           = $request->interaction[0];
            $rooms_description->notes                 = $request->notes[0];
            $rooms_description->house_rules           = $request->house_rules[0];
            $rooms_description->neighborhood_overview = $request->neighborhood_overview[0];
            $rooms_description->transit               = $request->transit[0];
            $rooms_description->save();

            $count=count($request->name);

              for($i=1;$i<$count;$i++)
              {
                $lan_description  = new RoomsDescriptionLang;

                $lan_description->room_id         = $rooms->id;
                $lan_description->lang_code        = $request->language[$i-1];
                $lan_description->name            = $request->name[$i];
                $lan_description->summary         = $request->summary[$i];
                $lan_description->space           = $request->space[$i];
                $lan_description->access          = $request->access[$i];
                $lan_description->interaction     = $request->interaction[$i];
                $lan_description->notes           = $request->notes[$i];
                $lan_description->house_rules     = $request->house_rules[$i];
                $lan_description->neighborhood_overview=$request->neighborhood_overview[$i];
                $lan_description->transit         = $request->transit[$i];
                $lan_description->save();

            }

            $multiple_rooms_bed_type = [];
           
                $multiple_rooms_bed_type = $request->multiple_rooms_bed_type;
          

            $id=array();
            // Multiple Rooms add function
          
                for($i=0;$i<count($request->room_name);$i++)
                {

                    // $multiple_rooms =new MultipleRooms;
                   if($request->room_name[$i] != '' && $request->room_description[$i] != '' && $request->room_night[$i] != '' ){
                        $multiple_rooms[$i] =array(
                            'room_id'     => $rooms->id,
                            'user_id'     => Rooms::find($rooms->id)->user_id,
                            'name'        => $request->room_name[$i],
                            'summary'     => $request->room_description[$i],
                            'room_type'   => $request->room_type_multiple[$i],
                            'accommodates'=> $request->room_accommodates[$i],
                            'number_of_rooms'=>(int)$request->number_of_rooms[$i],
                            'currency_code' => $request->room_currency_code[$i] ? $request->room_currency_code[$i] : 'USD',
                            'guests'      => $request->room_guests[$i],
                            'beds'        => $request->room_beds[$i],
                            'bed_type'    => $request->room_bed_type[$i] ? $request->room_bed_type[$i] : null,
                            'bedrooms'    => $request->room_bedrooms[$i],
                            'bathrooms'   => $request->room_bathrooms[$i],
                            'night'       => $request->room_night[$i],
                            'security'    => (@$request->room_security[$i])?@$request->room_security[$i]:'0',
                            'cleaning'    => $request->room_cleaning[$i],
                            'additional_guest' => $request->additional_guest_fee[$i],
                            'weekend'     => $request->weekend_price[$i],
                            'amenities'   => @implode(',', @$request->room_amenities[$i+1]),
                            'weekend'     => $request->weekend_price[$i],
                            'infants_allowed' => (@$request->infants_allowed[$i]=='true')?'Yes':'No',
                            'started'     => 'Yes',
                            //'status'      => 'Listed',
                            'calendar_type' => $request->calendar,
                            'cancel_policy' => $request->cancel_policy,
                            );
                        $id[]=DB::table('multiple_rooms')->insertGetId($multiple_rooms[$i]);

                        $multiple_step_status = new MultipleRoomsStepStatus;
                        $multiple_step_status->id = $id[$i];
                        
                        $multiple_step_status->save();
                        $total_beds_count = 0;

                        if(count($multiple_rooms_bed_type[$i])){
                            for($k=0;$k<count($multiple_rooms_bed_type[$i]);$k++){
                                $rooms_bed_type  =  new RoomsBedType;
                                $rooms_bed_type->room_id  = $id[$i];
                                $rooms_bed_type->type     = 'Multiple';
                                $rooms_bed_type->bed_type = $multiple_rooms_bed_type[$i][$k]['bed_type'];
                                $rooms_bed_type->beds     = $multiple_rooms_bed_type[$i][$k]['beds'];
                                $total_beds_count        += $multiple_rooms_bed_type[$i][$k]['beds'];
                                $rooms_bed_type->save();
                            }
                        }
                        $multi_room = MultipleRooms::find($id[$i]);
                        $multi_room->beds = $total_beds_count;
                        $multi_room->save();
                     
                        $multiple_length_of_stay_rules =  $request->rooms_length_of_stay ?: array();
                        
                        if(count($multiple_length_of_stay_rules)){
                            if(isset($multiple_length_of_stay_rules[$i])){
                                foreach($multiple_length_of_stay_rules[$i] as $rule) {
                                    if(@$rule['id']) {
                                        $check = [
                                            'id' => $rule['id'],
                                            'room_id' => $rooms->id,
                                            'multiple_room_id' => $id[$i],
                                            'type'    => 'length_of_stay',
                                        ];
                                    }
                                    else {
                                        $check = [
                                            'room_id' => $rooms->id,
                                            'multiple_room_id' => $id[$i],
                                            'type'    => 'length_of_stay',
                                            'period'  => $rule['period']
                                        ];
                                    }
                                    $multiple_price_rule = MultipleRoomsPriceRules::firstOrNew($check);
                                    $multiple_price_rule->room_id = $rooms->id;
                                    $multiple_price_rule->multiple_room_id = $id[$i];
                                    $multiple_price_rule->type =  'length_of_stay';
                                    $multiple_price_rule->period = $rule['period'];
                                    $multiple_price_rule->discount = $rule['discount'];

                                    $multiple_price_rule->save();
                                }
                            }
                        }

                        $multiple_early_birds = $request->rooms_early_bird ? $request->rooms_early_bird : array();

                        if(count($multiple_early_birds)){

                            if(isset($multiple_early_birds[$i])){
                                foreach($multiple_early_birds[$i] as $rule) {
                                    if(@$rule['id']) {
                                        $check = [
                                            'id' => $rule['id'],
                                            'room_id' => $rooms->id,
                                            'multiple_room_id' => $id[$i],
                                            'type'    => 'early_bird',
                                        ];
                                    }
                                    else {
                                        $check = [
                                            'room_id' => $rooms->id,
                                            'multiple_room_id' => $id[$i],
                                            'type'    => 'early_bird',
                                            'period'  => $rule['period']
                                        ];
                                    }
                                    $multiple_price_rule = MultipleRoomsPriceRules::firstOrNew($check);
                                    $multiple_price_rule->room_id = $rooms->id;
                                    $multiple_price_rule->multiple_room_id = $id[$i];
                                    $multiple_price_rule->type =  'early_bird';
                                    $multiple_price_rule->period = $rule['period'];
                                    $multiple_price_rule->discount = $rule['discount'];

                                    $multiple_price_rule->save();

                                }
                            }
                        }

                        $multiple_last_min = $request->rooms_last_min ? $request->rooms_last_min : array();

                        if(count($multiple_last_min)){

                            if(isset($multiple_last_min[$i])){
                                foreach($multiple_last_min[$i] as $rule) {
                                    if(@$rule['id']) {
                                        $check = [
                                            'id' => $rule['id'],
                                            'room_id' => $rooms->id,
                                            'multiple_room_id' => $id[$i],
                                            'type'    => 'last_min',
                                        ];
                                    }
                                    else {
                                        $check = [
                                            'room_id' => $rooms->id,
                                            'multiple_room_id' => $id[$i],
                                            'type'    => 'last_min',
                                            'period'  => $rule['period']
                                        ];
                                    }
                                    $multiple_price_rule = MultipleRoomsPriceRules::firstOrNew($check);
                                    $multiple_price_rule->room_id = $rooms->id;
                                    $multiple_price_rule->multiple_room_id = $id[$i];
                                    $multiple_price_rule->type =  'last_min';
                                    $multiple_price_rule->period = $rule['period'];
                                    $multiple_price_rule->discount = $rule['discount'];

                                    $multiple_price_rule->save();

                                }
                            }
                        }

                        $multiple_availability_rules = $request->rooms_availability_rules ? $request->rooms_availability_rules : array();
                         if(count($multiple_availability_rules)){
                            if(isset($multiple_availability_rules[$i])){
                                foreach($multiple_availability_rules[$i] as $rule) {

                                    if(@$rule['edit'] == 'true')
                                    {
                                        continue;
                                    }
                                    $check = [
                                        'id' => @$rule['id'] ?: '',
                                    ];

                                    $multiple_availability_rule = MultipleRoomsAvailabilityRules::firstOrNew($check);
                                    $multiple_availability_rule->room_id = $rooms->id;
                                    $multiple_availability_rule->multiple_room_id = $id[$i];
                                    $multiple_availability_rule->start_date = @$rule['start_date'] ? date('Y-m-d', $this->helper->custom_strtotime(@$rule['start_date'], PHP_DATE_FORMAT)) : date('Y-m-d');
                                    $multiple_availability_rule->end_date = @$rule['end_date'] ? date('Y-m-d', $this->helper->custom_strtotime(@$rule['end_date'], PHP_DATE_FORMAT)) : date('Y-m-d',strtotime(date('Y-m-d') . ' +1 day'));
                                    $multiple_availability_rule->minimum_stay = @$rule['minimum_stay'] ?: null;
                                    $multiple_availability_rule->maximum_stay = @$rule['maximum_stay'] ?: null;
                                    $multiple_availability_rule->type = @$rule['type'] != 'prev' ? @$rule['type']: @$multiple_availability_rule->type;

                                    $multiple_availability_rule->save();
                                
                                    /*$multiple_room = MultipleRooms::find($id[$i]);
                                    $multiple_room->minimum_stay = $request->rooms_minimum_stay[$i] ?: null;
                                    $multiple_room->maximum_stay = $request->rooms_maximum_stay[$i] ?: null;
                                    $multiple_room->save();*/
                                }
                            }
                        }
                    


                        $multiple_rooms_minimum_stay = @$request->rooms_minimum_stay ? $request->rooms_minimum_stay : array();

                         if(count($multiple_rooms_minimum_stay)){
                            if(isset($multiple_rooms_minimum_stay[$i])){
                                foreach($multiple_rooms_minimum_stay as $rule) {
                                    $multiple_room = MultipleRooms::find($id[$i]);
                                    $multiple_room->minimum_stay = @$request->rooms_minimum_stay[$i] ?: null;
                                    $multiple_room->maximum_stay = @$request->rooms_maximum_stay[$i] ?: null;
                                    $multiple_room->save();
                                }
                            }
                        }

                        if(isset($_FILES["room_photos"]["name"][$i]))
                        {
                            foreach($_FILES["room_photos"]["error"][$i] as $key=>$error) 
                            {
                               
                                $tmp_name = $_FILES["room_photos"]["tmp_name"][$i][$key];

                                $name = str_replace(' ', '_', $_FILES["room_photos"]["name"][$i][$key]);
                                
                                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                                $name = time().'_'.$name;

                                $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/images/multiple_rooms/'.$id[$i];
                                
                                if(!file_exists($filename))
                                {
                                    mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/multiple_rooms/'.$id[$i], 0777, true);
                                }

                                $fileSize = $_FILES["room_photos"]["size"][$i][$key];
                                $fileSizeKB = ($fileSize / 1024);
                                $fileSizeMB = ($fileSizeKB / 1024);
                                if($fileSizeMB > 10){
                                    $this->helper->flash_message('error', 'Maximum size for photo upload is 10 MB');
                                    return redirect(ADMIN_URL.'/rooms');
                                }
                                                           
                                if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif')   
                                {            
                                // if($this->helper->compress_image($tmp_name, "images/rooms/".$rooms->id."/".$name, 80))
                                try{
                                    if(move_uploaded_file($tmp_name, "images/multiple_rooms/".$id[$i]."/".$name))
                                    {
                                        
                                        $this->helper->compress_image("images/multiple_rooms/".$id[$i]."/".$name, "images/multiple_rooms/".$id[$i]."/".$name, 80, 1440, 960);
                                        $this->helper->compress_image("images/multiple_rooms/".$id[$i]."/".$name, "images/multiple_rooms/".$id[$i]."/".$name, 80, 1349, 402);
                                        $this->helper->compress_image("images/multiple_rooms/".$id[$i]."/".$name, "images/multiple_rooms/".$id[$i]."/".$name, 80, 450, 250);

                                        $photos          = new MultipleRoomImages;
                                        $photos->multiple_room_id  = $id[$i];
                                        $photos->room_id = $rooms->id;
                                        $photos->name    = $name;
                                        $photos->save();
                                    }
                                }catch (\Exception $e) {
                                   
                                        $this->helper->flash_message('error', 'Error uploading image! Please try uploading a different image!!!');
                                        return back();
                                    }
                                }
                            }

                            $photos_featured = MultipleRoomImages::where('multiple_room_id',$id[$i])->where('featured','Yes');
                            $photos_details = MultipleRoomImages::where('multiple_room_id',$id[$i])->get();
                           
                            if($photos_featured->count() == 0 && count($photos_details) != 0)
                            {
                                $photos = MultipleRoomImages::where('multiple_room_id',$id[$i])->first();
                                $photos->featured = 'Yes';
                                $photos->save();
                            }
                            
                        }

                    }   
                    
                }

                foreach ($id as $key => $value) {
                    $multiplerooms1  = MultipleRooms::find($value);
                    $multiplerooms1->status        = 'Listed';
                    $multiplerooms1->save();

                    $multiple_step_status = MultipleRoomsStepStatus::find($value);
                    
                    $multiple_step_status->basics = 1;
                    $multiple_step_status->description = 1;
                    $multiple_step_status->photos = 1;
                    $multiple_step_status->pricing = 1;
                    $multiple_step_status->calendar = 1;
                    $multiple_step_status->save();
                }





            if (@$request->bed_count != '') {
                foreach (@$request->bed_count as $k => $value) {
                    //dd($request->bed_types_name[$k]);
                    $alread_bed_available = RoomsBeds::where('room_id', $rooms->id)->where('bed_room_no', $value)->where('bed_id', $request->bed_id[$k])->first();
                    if ($alread_bed_available) {
                        $rooms_bedrooms = RoomsBeds::find($alread_bed_available->id);
                    } else {
                        $rooms_bedrooms = new RoomsBeds;

                    }
                    $rooms_bedrooms->room_id = $rooms->id;
                    $rooms_bedrooms->bed_room_no = $value;
                    $rooms_bedrooms->bed_id = $request->bed_id[$k];
                    $rooms_bedrooms->count = $request->bed_types_name[$k];
                    $rooms_bedrooms->save();

                }
            }

            //update Common Rooms beds
            if (@$request->common_bed_count != '') {
            foreach (@$request->common_bed_count as $k => $value) {
                //dd($request->bed_types_name[$k]);
                $alread_bed_available = RoomsBeds::where('room_id', $rooms->id)->where('bed_room_no', $value)->where('bed_id', $request->common_bed_id[$k])->first();
                if ($alread_bed_available) {
                    $rooms_bedrooms = RoomsBeds::find($alread_bed_available->id);
                } else {
                    $rooms_bedrooms = new RoomsBeds;

                }
                $rooms_bedrooms->room_id = $rooms->id;
                $rooms_bedrooms->bed_room_no = $value;
                $rooms_bedrooms->bed_id = $request->common_bed_id[$k];
                $rooms_bedrooms->count = $request->common_bed_types_name[$k];
                $rooms_bedrooms->save();

            }
        }


        if (@$request->type== 'Single') {
            $rooms_price = new RoomsPrice;
            $rooms_price->room_id          = $rooms->id;
            $rooms_price->night            = $request->night;       
            $rooms_price->cleaning         = $request->cleaning;
            $rooms_price->additional_guest = $request->additional_guest;
            $rooms_price->guests           = ($request->additional_guest) ? $request->guests : '0';
            $rooms_price->security         = $request->security;
            $rooms_price->weekend          = $request->weekend;
            $rooms_price->currency_code    = $request->currency_code;
            $rooms_price->save();
        }
            // Image upload
            if(isset($_FILES["photos"]["name"])){
            foreach($_FILES["photos"]["error"] as $key=>$error) {
                $tmp_name = $_FILES["photos"]["tmp_name"][$key];
                $name = str_replace(' ', '_', $_FILES["photos"]["name"][$key]);              
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $name = time().$key.'_.'.$ext;
                $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/images/rooms/'.$rooms->id;          
                if(!file_exists($filename)){
                    mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/rooms/'.$rooms->id, 0777, true);
                }                            
                if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif')  {    
                    if(UPLOAD_DRIVER=='cloudinary'){
                        $name = @$photos_uploaded[$key];
                    }
                    else{
                        if($ext == 'gif'){
                          move_uploaded_file($tmp_name, "images/rooms/".$rooms->id."/".$name);
                        }
                        else{
                            if(move_uploaded_file($tmp_name, "images/rooms/".$rooms->id."/".$name)){
                                $this->helper->compress_image("images/rooms/".$rooms->id."/".$name, "images/rooms/".$rooms->id."/".$name, 80, 1440, 960);
                                $this->helper->compress_image("images/rooms/".$rooms->id."/".$name, "images/rooms/".$rooms->id."/".$name, 80, 1349, 402);
                                $this->helper->compress_image("images/rooms/".$rooms->id."/".$name, "images/rooms/".$rooms->id."/".$name, 80, 450, 250);
                            }
                        }
                    }
                    $photos          = new RoomsPhotos;
                    $photos->room_id = $rooms->id;
                    $photos->name    = $name;
                    $photos->save();      
                }
            }
                
            }

            $rooms1  = Rooms::find($rooms->id);
            $rooms1->status        = 'Listed';
           
                $all_multi_room = MultipleRooms::where('room_id',$rooms->id)->pluck('id');
                $total_beds_count = RoomsBedType::whereIn('room_id',$all_multi_room)->sum('beds');
                $rooms1->beds = $total_beds_count;
           

            $rooms1->save();

            $rooms_steps = new RoomsStepsStatus;

            $rooms_steps->room_id     = $rooms->id;
            $rooms_steps->basics      = 1;
            $rooms_steps->description = 1;
            $rooms_steps->location    = 1;
            $rooms_steps->photos      = 1;
            $rooms_steps->pricing     = 1;
            $rooms_steps->calendar    = 1;

            $rooms_steps->save();

            $length_of_stay_rules =  $request->length_of_stay ?: array();
            foreach($length_of_stay_rules as $rule) {
                if(@$rule['id']) {
                    $check = [
                        'id' => $rule['id'],
                        'room_id' => $rooms->id,
                        'type'    => 'length_of_stay',
                    ];
                }
                else {
                    $check = [
                        'room_id' => $rooms->id,
                        'type'    => 'length_of_stay',
                        'period'  => $rule['period']
                    ];
                }
                $price_rule = RoomsPriceRules::firstOrNew($check);
                $price_rule->room_id = $rooms->id;
                $price_rule->type =  'length_of_stay';
                $price_rule->period = $rule['period'];
                $price_rule->discount = $rule['discount'];

                $price_rule->save();
            }

            $early_bird_rules = $request->early_bird ?: array();
            foreach($early_bird_rules as $rule) {
                if(@$rule['id']) {
                    $check = [
                        'id' => $rule['id'],
                        'room_id' => $rooms->id,
                        'type'    => 'early_bird',
                    ];
                }
                else {
                    $check = [
                        'room_id' => $rooms->id,
                        'type'    => 'early_bird',
                        'period'  => $rule['period']
                    ];
                }
                $price_rule = RoomsPriceRules::firstOrNew($check);
                $price_rule->room_id = $rooms->id;
                $price_rule->type =  'early_bird';
                $price_rule->period = $rule['period'];
                $price_rule->discount = $rule['discount'];

                $price_rule->save();
            }

            $last_min_rules = $request->last_min ?: array();
            foreach($last_min_rules as $rule) {
                if(@$rule['id']) {
                    $check = [
                        'id' => $rule['id'],
                        'room_id' => $rooms->id,
                        'type'    => 'last_min',
                    ];
                }
                else {
                    $check = [
                        'room_id' => $rooms->id,
                        'type'    => 'last_min',
                        'period'  => $rule['period']
                    ];
                }
                $price_rule = RoomsPriceRules::firstOrNew($check);
                $price_rule->room_id = $rooms->id;
                $price_rule->type =  'last_min';
                $price_rule->period = $rule['period'];
                $price_rule->discount = $rule['discount'];

                $price_rule->save();
            }        

            $availability_rules = $request->availability_rules ?: array();
            foreach($availability_rules as $rule) {
                if(@$rule['edit'] == 'true')
                {
                    continue;
                }
                $check = [
                    'id' => @$rule['id'] ?: '',
                ];
                $availability_rule = RoomsAvailabilityRules::firstOrNew($check);
                $availability_rule->room_id = $rooms->id;
                $availability_rule->start_date = date('Y-m-d', $this->helper->custom_strtotime(@$rule['start_date'], PHP_DATE_FORMAT));
                $availability_rule->end_date = date('Y-m-d', $this->helper->custom_strtotime(@$rule['end_date'], PHP_DATE_FORMAT));
                $availability_rule->minimum_stay = @$rule['minimum_stay'] ?: null;
                $availability_rule->maximum_stay = @$rule['maximum_stay'] ?: null;
                $availability_rule->type = @$rule['type'] != 'prev' ? @$rule['type']: @$availability_rule->type;
                $availability_rule->save();
            }
            if (@$request->type== 'Single') {
                $rooms_price = RoomsPrice::find($rooms->id);
                $rooms_price->minimum_stay = $request->minimum_stay ?: null;
                $rooms_price->maximum_stay = $request->maximum_stay ?: null;
                $rooms_price->save();
            }

            \DB::commit();

            flash_message('success', 'Room Added Successfully'); 
            return redirect(ADMIN_URL.'/rooms');
        }catch (\Exception $e) {
                \DB::rollback();
                dd($e);
                flash_message('error',$e->getMessage()); 
                return redirect(ADMIN_URL.'/rooms');
            }
        }
        else
        {
            return redirect(ADMIN_URL.'/rooms');
        }
    }

       //update validation function 
      public function update_price(Request $request)
    {          

        $minimum_amount = $this->payment_helper->currency_convert(DEFAULT_CURRENCY,$request->currency_code, MINIMUM_AMOUNT); 
        $currency_symbol = Currency::whereCode($request->currency_code)->first()->original_symbol;
   if(isset($request->night) || isset($request->week) || isset($request->month))
   {
        $night_price = $request->night;        $week_price = $request->week;       $month_price = $request->month;

         // all error validation check
         if(isset($request->night) && isset($request->week) && isset($request->month))
        {
            if($night_price < $minimum_amount && $week_price < $minimum_amount && $month_price < $minimum_amount)
                {
                     return json_encode(['success'=>'all_error','msg' => trans('validation.min.numeric', ['attribute' => trans('messages.inbox.price'), 'min' => $currency_symbol.$minimum_amount]), 'attribute' => 'price', 'currency_symbol' => $currency_symbol,'min_amt' => $minimum_amount]);

                }
        }
         // night validation check
        if(isset($request->night))
        {
            $night_price = $request->night; 
            if($night_price < $minimum_amount)
            {
                return json_encode(['success'=>'night_false','msg' => trans('validation.min.numeric', ['attribute' => trans('messages.inbox.price'), 'min' => $currency_symbol.$minimum_amount]), 'attribute' => 'price', 'currency_symbol' => $currency_symbol,'min_amt' => $minimum_amount,'val' => $night_price]);
            }else  {   return json_encode(['success'=>'true','msg' => 'true']);     }  
        }
         // week validation check
        elseif(isset($request->week) && @$request->week !='0')
            {
                $week_price = $request->week; 
                if($week_price < $minimum_amount)
                {
                    return json_encode(['success'=>'week_false','msg' => trans('validation.min.numeric', ['attribute' => 'price', 'min' => $currency_symbol.$minimum_amount]), 'attribute' => 'week', 'currency_symbol' => $currency_symbol,'val' => $week_price]);
                }else
                {
                    return json_encode(['success'=>'true','msg' => 'true']);
                }                                    
            }
        // month validation check
       elseif(isset($request->month) && @$request->month !='0')
           {
                $month_price = $request->month; 
                if($month_price < $minimum_amount)
                {
                    return json_encode(['success'=>'month_false','msg' => trans('validation.min.numeric', ['attribute' => 'price', 'min' => $currency_symbol.$minimum_amount]), 'attribute' => 'month', 'currency_symbol' => $currency_symbol,'val' => $month_price]);
                }else
                {
                    return json_encode(['success'=>'true','msg' => 'true']);
                }  
                
           } 

        else {  return json_encode(['success'=>'true','msg' => 'true']); }  
    }  
         

    }

    /**
     * Update Room Details
     *
     * @param array $request    Input values
     * @return redirect     to Rooms View
     */
    public function update(Request $request, CalendarController $calendar)
    {
        // dd($request->all());
        $rooms_id = Rooms::find($request->id); if(empty($rooms_id))  abort('404');
        if(!$_POST)
        {
            $bedrooms = [];
            $bedrooms[0] = 'Studio';
            for($i=1; $i<=10; $i++)
                $bedrooms[$i] = $i;

            $beds = [];
            for($i=1; $i<=16; $i++)
                $beds[$i] = ($i == 16) ? $i.'+' : $i;

            $bathrooms = [];
            $bathrooms[0] = 0;
            for($i=0.5; $i<=8; $i+=0.5)
                $bathrooms["$i"] = ($i == 8) ? $i.'+' : $i;
           
            $bathrooms1 = [];
            for($i=0; $i<=8; $i++)
                $bathrooms1["$i"] = ($i == 8) ? $i.'+' : $i;

            $accommodates = [];
            for($i=1; $i<=16; $i++)
                $accommodates[$i] = ($i == 16) ? $i.'+' : $i;

            $data['bedrooms']      = $bedrooms;
            $data['beds']          = array_values($beds);
            $data['bed_type'] = BedType::active_all();
             $data['bed_types']     = BedType::active_all();
            // $data['bed_type']      = BedType::where('status','Active')->pluck('name','id');
            $data['bathrooms']     = $bathrooms;
            $data['bathrooms1']    = $bathrooms1;
            $data['property_type'] = PropertyType::where('status','Active')->pluck('name','id');
            $data['room_type_multiple']     = RoomType::where(['status'=>'Active'])->pluck('name','id');
            $data['room_type']     = RoomType::where('status','Active')->pluck('name','id');
            $data['lan_description']=RoomsDescriptionLang::where('room_id',$request->id)->get();
            $data['accommodates']  = $accommodates;
            $data['country']       = Country::pluck('long_name','short_name');
            $data['amenities']     = Amenities::active_all();
            $data['amenities_type'] = AmenitiesType::active_all();
            $data['users_list']    = User::pluck('first_name','id');
            $data['room_id']       = $request->id;
            $data['result']        = Rooms::find($request->id);
            $data['rooms_bed_type_items'] = [];
            $data['get_single_bed_type'] = $data['result']->get_single_bed_type;
            $data['first_bed_type1'] = $data['result']->get_first_bed_type;
            //dd($data['first_bed_type1']);
            $data['get_common_bed_type'] = $data['result']->get_common_bed_type;
            $data['first_bed_type'] = BedType::where('status', 'Active')->limit(4)->get();

            if($data['result']->type == 'Single'){
                $data['sub_rooms'] = [];
                $data['sub_rooms1'] = [];
                $data['rooms_bed_type_items'] = RoomsBedType::where('room_id',$request->id)->get();
                
                $data['calendar']      = str_replace(['<form name="calendar-edit-form">','</form>', url('manage-listing/'.$request->id.'/calendar')], ['', '', 'javascript:void(0);'],$calendar->generate('main_room',$request->id));
            }else{
                
            $data['sub_rooms'] = MultipleRooms::where('room_id',$request->id)->where('status','Listed')->pluck('name','id');
            $data['sub_rooms1'] = MultipleRooms::where('room_id',$request->id)->pluck('name','id');
               
                $multiple_rooms = MultipleRooms::where('room_id',$request->id)->get();
                foreach($multiple_rooms as $k=>$multiple){
                    $data['calendar'][$k] = str_replace(['<form name="calendar-edit-form">','</form>', url('manage-listing/'.$request->id.'/calendar')], ['', '', 'javascript:void(0);'],$calendar->generate('sub_room',$multiple->id)); 
                    
                }  
            } 
           
            $data['get_bathrooms'] = $data['result']->get_bathrooms;
            $data['get_common_bathrooms'] = $data['result']->get_common_bathrooms;
            $data['firstbedtypeid'] = BedType::where('status', 'Active')->first()->id;

            $data['rooms_photos']  = RoomsPhotos::where('room_id',$request->id)->orderBy('id','asc')->get();
            
            $data['prev_amenities'] = explode(',', $data['result']->amenities);
            $data['multiple_rooms']  = MultipleRooms::where('room_id',$request->id)->get();

            $data['multiple_room_images']  = MultipleRoomImages::where('room_id',$request->id)->get();
            $data['length_of_stay_options'] = Rooms::getLenghtOfStayOptions();
            $data['availability_rules_months_options'] = Rooms::getAvailabilityRulesMonthsOptions();
           

            return view('admin.rooms.edit', $data);
        }
        else if($request->submit == 'basics')
        {
            $rooms = Rooms::find($request->room_id);
           
            $rooms->bedrooms            = $rooms->type == 'Single' ? $request->bedrooms : null;
            /*$rooms->beds          = $request->beds;
            $rooms->bed_type      = $request->bed_type;*/
           // $rooms->type                = $request->type;
            $rooms->bathrooms           = $rooms->type == 'Single' ? $request->bathrooms : null;
            $rooms->bathroom_shared     = $rooms->type == 'Single' ? $request->bathroom_shared : null;
            $rooms->property_type       = $request->property_type;
            $rooms->room_type           = $rooms->type == 'Single' ? $request->room_type : null;
            $rooms->accommodates        = $rooms->type == 'Single' ? $request->accommodates : null;
            //$rooms->room_type           = $request->room_type;
            //$rooms->accommodates        = $request->accommodates;

            $rooms->save();

            //update Rooms beds
            $room_beds = array();
            if (@$request->bed_count != '') {
                foreach (@$request->bed_count as $k => $value) {
                    //dd($request->bed_types_name[$k]);
                    $alread_bed_available = RoomsBeds::where('room_id', $request->room_id)->where('bed_room_no', $value)->where('bed_id', $request->bed_id[$k])->first();
                    if ($alread_bed_available) {
                        $rooms_bedrooms = RoomsBeds::find($alread_bed_available->id);
                    } else {
                        $rooms_bedrooms = new RoomsBeds;

                    }
                    $rooms_bedrooms->room_id = $request->room_id;
                    $rooms_bedrooms->bed_room_no = $value;
                    $rooms_bedrooms->bed_id = $request->bed_id[$k];
                    $rooms_bedrooms->count = $request->bed_types_name[$k];
                    $rooms_bedrooms->save();

                    $room_beds[] = $request->bed_id[$k];
                    if (!isset($request->bed_count[$k+1]) || $request->bed_count[$k]!=$request->bed_count[$k+1]) {
                        $alread_bed_available = RoomsBeds::where('room_id', $request->room_id)->where('bed_room_no', $value)->whereNotIn('bed_id', $room_beds)->delete();
                        $room_beds = array();
                    }

                }
            }

            //update Common Rooms beds
            foreach ($request->common_bed_count as $k => $value) {
                //dd($request->bed_types_name[$k]);
                $alread_bed_available = RoomsBeds::where('room_id', $request->room_id)->where('bed_room_no', $value)->where('bed_id', $request->common_bed_id[$k])->first();
                if ($alread_bed_available) {
                    $rooms_bedrooms = RoomsBeds::find($alread_bed_available->id);
                } else {
                    $rooms_bedrooms = new RoomsBeds;

                }
                $rooms_bedrooms->room_id = $request->room_id;
                $rooms_bedrooms->bed_room_no = $value;
                $rooms_bedrooms->bed_id = $request->common_bed_id[$k];
                $rooms_bedrooms->count = $request->common_bed_types_name[$k];
                $rooms_bedrooms->save();

            }
            
            $rooms_status = RoomsStepsStatus::find($request->room_id);
            $rooms_status->basics = 1;
            $rooms_status->save();

            $bed_type = $request->rooms_bed_type;
           
            $total_beds_count = 0;
           if (@$request->rooms_bed_type != '') {
            if(count($bed_type)){
                for($i=0;$i<count($bed_type);$i++){

                    if(@$bed_type[$i]['id']) {
                        $check = [
                            'id' => $bed_type[$i]['id'],
                            'room_id' => $rooms->id,
                            'type'    => 'Single',
                        ];
                    }
                    else {
                        $check = [
                            'room_id' => $rooms->id,
                            'type'    => 'Single',
                            'bed_type'  => $bed_type[$i]['bed_type'],
                        ];
                    }

                    $rooms_bed_type  =  RoomsBedType::firstOrNew($check);;
                    $rooms_bed_type->room_id  = $rooms->id;
                    $rooms_bed_type->type     = 'Single';
                    $rooms_bed_type->bed_type = $bed_type[$i]['bed_type'];
                    $rooms_bed_type->beds     = $bed_type[$i]['beds'];
                    $total_beds_count += $bed_type[$i]['beds'];
                    $rooms_bed_type->save();

                }
            }
            }
            $rooms->beds = $total_beds_count;
            $rooms->save();

            $this->roomStatusUpdate($request->room_id);

            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'booking_type')
        {
            $rooms = Rooms::find($request->room_id);

            $rooms->booking_type  = $request->booking_type;

            $rooms->save();
            
            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'description')
        {
            $rooms = Rooms::find($request->room_id);
            
            $rooms->name          = $request->name[0];
             $rooms->sub_name      = $rooms->type=='Single'?RoomType::find($request->room_type)->name.' in '.$request->city:$request->city;
           // $rooms->sub_name      = RoomType::find($request->room_type)->name.' in '.$request->city;
            $rooms->summary       = $request->summary[0];

            $rooms->save();

            $rooms_description = RoomsDescription::find($request->room_id);

            $rooms_description = RoomsDescription::find($request->room_id);
            $rooms_description->space                 = $request->space[0];
            $rooms_description->access                = $request->access[0];
            $rooms_description->interaction           = $request->interaction[0];
            $rooms_description->notes                 = $request->notes[0];
            $rooms_description->house_rules           = $request->house_rules[0];
            $rooms_description->neighborhood_overview = $request->neighborhood_overview[0];
            $rooms_description->transit               = $request->transit[0];
            $rooms_description->save();
            
             RoomsDescriptionLang::where('room_id',$request->id)->delete();
            $count=count($request->name);
              for($i=1;$i<$count;$i++){
                $lan_description           =  new RoomsDescriptionLang;
                $lan_description->room_id     = $rooms->id;
                $lan_description->lang_code    = $request->language[$i-1];
                $lan_description->name        = $request->name[$i];
                $lan_description->summary     = $request->summary[$i];
                $lan_description->space       = $request->space[$i];
                $lan_description->access      = $request->access[$i];
                $lan_description->interaction = $request->interaction[$i];
                $lan_description->notes       = $request->notes[$i];
                $lan_description->house_rules = $request->house_rules[$i];
                $lan_description->neighborhood_overview = $request->neighborhood_overview[$i];
                $lan_description->transit     = $request->transit[$i];
                $lan_description->save();
              }

            $rooms_status = RoomsStepsStatus::find($request->room_id);
            $rooms_status->description = 1;
            $rooms_status->save();

            $this->roomStatusUpdate($request->room_id);
            
            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'location')
        {
            $latt=$request->latitude;
            $longg=$request->longitude;
            if($latt==''||$longg==''){
                $address        =$request->address_line_1.' '.$request->address_line_2.' '.$request->city.' '.$request->state.' '.$request->country;
                $latlong=$this->latlong($address);

                $latt       = $latlong['lat'];
                $longg      = $latlong['long'];
            }

            $rooms_address = RoomsAddress::find($request->room_id);

            $rooms_address->address_line_1 = $request->address_line_1;
            $rooms_address->address_line_2 = $request->address_line_2;
            $rooms_address->city           = $request->city;
            $rooms_address->state          = $request->state;
            $rooms_address->country        = $request->country;
            $rooms_address->postal_code    = $request->postal_code;
            $rooms_address->latitude       = $latt;
            $rooms_address->longitude      = $longg;

            $rooms_address->save();

            $rooms_status = RoomsStepsStatus::find($request->room_id);
            $rooms_status->location = 1;
            $rooms_status->save();

            $this->roomStatusUpdate($request->room_id);
            
            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'amenities')
        {
            $rooms = Rooms::find($request->room_id);
            
            $rooms->amenities     = @implode(',', @$request->amenities);

            $rooms->save();
            
            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'photos')
        {

            $delete = RoomsPhotos::where('room_id',$request->room_id)->delete();
          
            if($request->hidden_image !=''){
                $i= 0;
                foreach(@$request->hidden_image as $image){
                    if($image != ''){
                        $rooms_photo = new RoomsPhotos;
                        $rooms_photo->room_id = $request->room_id;
                        $rooms_photo->name = $image;
                        $rooms_photo->highlights = $request->hidden_high[$i];
                        $rooms_photo->save();
                        $i++;
                    }
                }
            }
            // Image upload
            if(isset($_FILES["photos"]["name"])){
                foreach($_FILES["photos"]["error"] as $key=>$error) 
                {
                    $tmp_name = $_FILES["photos"]["tmp_name"][$key];

                    $name = str_replace(' ', '_', $_FILES["photos"]["name"][$key]);
                    
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    $name = time().$key.'_.'.$ext;

                    $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/images/rooms/'.$request->room_id;
                                    
                    if(!file_exists($filename))
                    {
                        mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/rooms/'.$request->room_id, 0777, true);
                    }
                                               
                    if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif')   
                    {            
                        if(UPLOAD_DRIVER=='cloudinary')
                        {
                            $c=$this->helper->cloud_upload($tmp_name);
                            if($c['status']!="error")
                            {
                                $name=$c['message']['public_id'];    
                            }
                            else
                            {
                                flash_message('danger', $c['message']); // Call flash message function
                                return redirect(ADMIN_URL.'/rooms');
                            }
                        }
                        else
                        {
                            if($ext=='gif')
                            {

                                move_uploaded_file($tmp_name, "images/rooms/".$request->id."/".$name);
                            }
                            else
                            {

                                if(move_uploaded_file($tmp_name, "images/rooms/".$request->room_id."/".$name))
                                {
                                    $this->helper->compress_image("images/rooms/".$request->room_id."/".$name, "images/rooms/".$request->room_id."/".$name, 80, 1440, 960);
                                    $this->helper->compress_image("images/rooms/".$request->room_id."/".$name, "images/rooms/".$request->room_id."/".$name, 80, 1349, 402);
                                    $this->helper->compress_image("images/rooms/".$request->room_id."/".$name, "images/rooms/".$request->room_id."/".$name, 80, 450, 250);
                                }
                            }
                        }
                        $photos          = new RoomsPhotos;
                        $photos->room_id = $request->room_id;
                        $photos->name    = $name;
                        $photos->save();        
                        
                    }
                }

                // $photos_featured = RoomsPhotos::where('room_id',$request->room_id)->where('featured','Yes');
                // if($photos_featured->count() == 0){
                //     $photos = RoomsPhotos::where('room_id',$request->room_id)->first();
                //     $photos->featured = 'Yes';
                //     $photos->save();
                // }
            }

            $rooms_status = RoomsStepsStatus::find($request->room_id);
            $rooms_status->photos = 1;
            $rooms_status->save();
            $this->roomStatusUpdate($request->room_id);

            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'video')
        {
            $rooms = Rooms::find($request->room_id);

            $search     = '#(.*?)(?:href="https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch?.*?v=))([\w\-]{10,12}).*#x';
                $count      = preg_match($search, $request->video);
                $rooms      = Rooms::find($request->id); 
                if($count == 1) {
                    $replace    = 'https://www.youtube.com/embed/$2';
                    $video      = preg_replace($search,$replace,$request->video);
                    $rooms->video = $video;
                }
                else {
                    $rooms->video = $request->video;
                }

            $rooms->save();
            
            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'pricing')
        {
            $rooms_price = RoomsPrice::find($request->room_id);

            $rooms_price->night            = $request->night;
            // $rooms_price->week             = $request->week;
            // $rooms_price->month            = $request->month;
            $rooms_price->cleaning         = $request->cleaning;
            $rooms_price->additional_guest = $request->additional_guest;
            $rooms_price->guests           = ($request->additional_guest) ? $request->guests : '0';
            $rooms_price->security         = $request->security;
            $rooms_price->weekend          = $request->weekend;
            $rooms_price->currency_code    = $request->currency_code;

            $rooms_price->save();

            $rooms_status = RoomsStepsStatus::find($request->room_id);
            $rooms_status->pricing = 1;
            $rooms_status->save();
            $this->roomStatusUpdate($request->room_id);

            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'pricing')
        {
            $rooms_price = RoomsPrice::find($request->room_id);

            $rooms_price->night            = $request->night;
            // $rooms_price->week             = $request->week;
            // $rooms_price->month            = $request->month;
            $rooms_price->cleaning         = $request->cleaning;
            $rooms_price->additional_guest = $request->additional_guest;
            $rooms_price->guests           = ($request->additional_guest) ? $request->guests : '0';
            $rooms_price->security         = (@$request->security)?@$request->security:'0';
            $rooms_price->weekend          = $request->weekend;
            $rooms_price->currency_code    = $request->currency_code;

            $rooms_price->save();
            
            $this->helper->flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'multiple_rooms')
        {
         
            $k=0;
            $images_array_count = count($_FILES)-1 ;
            //dd($request->room_name);
           
           for($i=0;$i<count($request->room_name) ;$i++){

                if ( ! isset($request->img_id[$i]))
                {

                       $multiple_rooms[$i] = new MultipleRooms;
                }
                else
                {

                    $multiple_rooms[$i] = MultipleRooms::find($request->img_id[$i]);
                        if(count($multiple_rooms[$i]) == 0)
                        {

                            $multiple_rooms[$i] = new MultipleRooms;
                        }
                }

                if($request->mulitple_room_id[$i]){
                    $multiple_rooms[$i] = MultipleRooms::find($request->mulitple_room_id[$i]);

                    $multiple_rooms_step[$i] = MultipleRoomsStepStatus::find($request->mulitple_room_id[$i]);
                }

                if(!$multiple_rooms[$i]){
                    $multiple_rooms[$i] = new MultipleRooms;

                    $multiple_rooms_step[$i] = new MultipleRoomsStepStatus;
                }

                    if($request->room_name[$i] != '' && $request->room_description[$i] != '' ){
                        $multiple_rooms[$i]->name =  $request->room_name[$i];
                        
                        $multiple_rooms[$i]->summary =  $request->room_description[$i];
                                            
                        $multiple_rooms[$i]->accommodates = $request->room_accommodates[$i];
                        $multiple_rooms[$i]->infants_allowed = (@$request->infants_allowed[$i]=='true')?'Yes':'No';
                        $multiple_rooms[$i]->number_of_rooms = $request->number_of_rooms[$i];
                        $multiple_rooms[$i]->beds       =  $request->room_beds[$i];
                        $multiple_rooms[$i]->bed_type   =  $request->room_bed_type[$i] ? $request->room_bed_type[$i] : null;
                        $multiple_rooms[$i]->room_id    =  $request->room_id;
                        $multiple_rooms[$i]->room_type  = $request->room_type_multiple[$i];
                        $multiple_rooms[$i]->user_id    =  Rooms::find($request->room_id)->user_id;
                        $multiple_rooms[$i]->guests     =  $request->room_guests[$i];
                        $multiple_rooms[$i]->bedrooms   =  $request->room_bedrooms[$i];
                        $multiple_rooms[$i]->bathrooms  =  $request->room_bathrooms[$i];
                        $multiple_rooms[$i]->night      =  $request->room_night[$i];
                        $multiple_rooms[$i]->security   = (@$request->room_security[$i])?@$request->room_security[$i]:'0';
                        $multiple_rooms[$i]->cleaning   = $request->room_cleaning[$i];
                        $multiple_rooms[$i]->additional_guest = $request->additional_guest_fee[$i];
                        $multiple_rooms[$i]->weekend    = $request->weekend_price[$i];
                        $multiple_rooms[$i]->amenities  = @implode(',', @$request->room_amenities[$i]);
                        $multiple_rooms[$i]->weekend    = $request->weekend_price[$i];
                        $multiple_rooms[$i]->currency_code = $request->room_currency_code[$i] ? $request->room_currency_code[$i] : 'USD';
                        $multiple_rooms[$i]->started = 'Yes';
                        $multiple_rooms[$i]->save();

                        $bed_type = array();
                        $bed_type = $request->multiple_rooms_bed_type;

                       
                        $total_beds_count = 0;                        
                      
                        if(count($bed_type[$i])){
                            for($mr=0;$mr<count($bed_type[$i]);$mr++){

                                if(@$bed_type[$i][$mr]['id']) {
                                    $check = [
                                        'id' => $bed_type[$i][$mr]['id'],
                                        'room_id' => $multiple_rooms[$i]->id,
                                        'type'    => 'Multiple',
                                    ];
                                }
                                else {
                                    $check = [
                                        'room_id' => $multiple_rooms[$i]->id,
                                        'type'    => 'Multiple',
                                        'bed_type'  => $bed_type[$i][$mr]['bed_type'],
                                    ];
                                }
                                
                                $rooms_bed_type  =  RoomsBedType::firstOrNew($check);;
                                $rooms_bed_type->room_id  = $multiple_rooms[$i]->id;
                                $rooms_bed_type->type     = 'Multiple';
                                $rooms_bed_type->bed_type = $bed_type[$i][$mr]['bed_type'];
                                $rooms_bed_type->beds     = $bed_type[$i][$mr]['beds'];
                                $total_beds_count += $bed_type[$i][$mr]['beds'] ? $bed_type[$i][$mr]['beds'] : 0;
                                $rooms_bed_type->save();
                            }
                        }
            
                        $multiple_rooms[$i]->beds = $total_beds_count;
                        $multiple_rooms[$i]->save();
                        $multiple_length_of_stay_rules =  $request->rooms_length_of_stay ?: array();
                        if(count($multiple_length_of_stay_rules)){
                            if(isset($multiple_length_of_stay_rules[$i])){
                                foreach($multiple_length_of_stay_rules[$i] as $rule) {
                                    if(@$rule['id']) {
                                        $check = [
                                            'id' => $rule['id'],
                                            'room_id' => $request->room_id,
                                            'multiple_room_id' => $multiple_rooms[$i]->id,
                                            'type'    => 'length_of_stay',
                                        ];
                                    }
                                    else {
                                        $check = [
                                            'room_id' => $request->room_id,
                                            'multiple_room_id' => $multiple_rooms[$i]->id,
                                            'type'    => 'length_of_stay',
                                            'period'  => $rule['period']
                                        ];
                                    }
                                    $multiple_price_rule = MultipleRoomsPriceRules::firstOrNew($check);
                                    $multiple_price_rule->room_id = $request->room_id;
                                    $multiple_price_rule->multiple_room_id = $multiple_rooms[$i]->id;
                                    $multiple_price_rule->type =  'length_of_stay';
                                    $multiple_price_rule->period = $rule['period'];
                                    $multiple_price_rule->discount = $rule['discount'];

                                    $multiple_price_rule->save();
                                }
                            }
                        }

                        $multiple_early_bird_rules =  $request->rooms_early_bird ?: array();
                        if(count($multiple_early_bird_rules)){
                            if(isset($multiple_early_bird_rules[$i])){
                                foreach($multiple_early_bird_rules[$i] as $rule) {
                                    if(@$rule['id']) {
                                        $check = [
                                            'id' => $rule['id'],
                                            'room_id' => $request->room_id,
                                            'multiple_room_id' => $multiple_rooms[$i]->id,
                                            'type'    => 'early_bird',
                                        ];
                                    }
                                    else {
                                        $check = [
                                            'room_id' => $request->room_id,
                                            'multiple_room_id' => $multiple_rooms[$i]->id,
                                            'type'    => 'early_bird',
                                            'period'  => $rule['period']
                                        ];
                                    }
                                    $multiple_price_rule = MultipleRoomsPriceRules::firstOrNew($check);
                                    $multiple_price_rule->room_id = $request->room_id;
                                    $multiple_price_rule->multiple_room_id = $multiple_rooms[$i]->id;
                                    $multiple_price_rule->type =  'early_bird';
                                    $multiple_price_rule->period = $rule['period'];
                                    $multiple_price_rule->discount = $rule['discount'];

                                    $multiple_price_rule->save();
                                }
                            }
                        }

                        $multiple_last_min_rules =  $request->rooms_last_min ?: array();
                        if(count($multiple_last_min_rules)){
                            if(isset($multiple_last_min_rules[$i])){
                                foreach($multiple_last_min_rules[$i] as $rule) {
                                    if(@$rule['id']) {
                                        $check = [
                                            'id' => $rule['id'],
                                            'room_id' => $request->room_id,
                                            'multiple_room_id' => $multiple_rooms[$i]->id,
                                            'type'    => 'last_min',
                                        ];
                                    }
                                    else {
                                        $check = [
                                            'room_id' => $request->room_id,
                                            'multiple_room_id' => $multiple_rooms[$i]->id,
                                            'type'    => 'last_min',
                                            'period'  => $rule['period']
                                        ];
                                    }
                                    $multiple_price_rule = MultipleRoomsPriceRules::firstOrNew($check);
                                    $multiple_price_rule->room_id = $request->room_id;
                                    $multiple_price_rule->multiple_room_id = $multiple_rooms[$i]->id;
                                    $multiple_price_rule->type =  'last_min';
                                    $multiple_price_rule->period = $rule['period'];
                                    $multiple_price_rule->discount = $rule['discount'];

                                    $multiple_price_rule->save();
                                }
                            }
                        }

                        $multiple_availability_rules = $request->rooms_availability_rules ?: array();

                        if(count($multiple_availability_rules)){
                            if(isset($multiple_availability_rules[$i])){
                                foreach($multiple_availability_rules[$i] as $rule) {
                                    if(@$rule['edit'] == 'true')
                                    {
                                        continue;
                                    }
                                    $check = [
                                        'id' => @$rule['id'] ?: '',
                                    ];
                                    $multiple_availability_rule = MultipleRoomsAvailabilityRules::firstOrNew($check);
                                    $multiple_availability_rule->room_id = $request->room_id;
                                    $multiple_availability_rule->multiple_room_id = $multiple_rooms[$i]->id;
                                    $multiple_availability_rule->start_date = @$rule['start_date'] ? date('Y-m-d', $this->helper->custom_strtotime(@$rule['start_date'], PHP_DATE_FORMAT)) : date('Y-m-d');
                                    $multiple_availability_rule->end_date = @$rule['end_date'] ? date('Y-m-d', $this->helper->custom_strtotime(@$rule['end_date'], PHP_DATE_FORMAT)) : date('Y-m-d',strtotime(date('Y-m-d') . ' +1 day'));
                                    $multiple_availability_rule->minimum_stay = @$rule['minimum_stay'] ?: null;
                                    $multiple_availability_rule->maximum_stay = @$rule['maximum_stay'] ?: null;
                                    if(@$rule['type'] != 'prev'){
                                        
                                        $multiple_availability_rule->type = @$rule['type'] != 'prev' ? @$rule['type']: @$multiple_availability_rule->type;
                                    }
                                    $multiple_availability_rule->save();
                                }
                                
                                /*$multiple_rooms_min = MultipleRooms::find($multiple_rooms[$i]->id);
                                $multiple_rooms_min->minimum_stay = $request->rooms_minimum_stay[$i] ?: null;
                                $multiple_rooms_min->maximum_stay = $request->rooms_maximum_stay[$i] ?: null;
                                $multiple_rooms_min->save();*/
                            }
                        }

                        $multiple_rooms_step[$i]->id = $multiple_rooms[$i]->id;
                        $multiple_rooms_step[$i]->basics = 1;
                        $multiple_rooms_step[$i]->description = 1;
                        $multiple_rooms_step[$i]->pricing = 1;
                        $multiple_rooms_step[$i]->calendar = 1;

                        $multiple_rooms_step[$i]->save();

                    }  

                

                        $multiple_rooms_minimum_stay = @$request->rooms_minimum_stay ? $request->rooms_minimum_stay : array();

                         if(count($multiple_rooms_minimum_stay)){
                            if(isset($multiple_rooms_minimum_stay[$i])){
                                foreach($multiple_rooms_minimum_stay as $rule) {
                                    $multiple_room = MultipleRooms::find($multiple_rooms[$i]->id);
                                    $multiple_room->minimum_stay = @$request->rooms_minimum_stay[$i] ?: null;
                                    $multiple_room->maximum_stay = @$request->rooms_maximum_stay[$i] ?: null;
                                    $multiple_room->save();
                                }
                            }
                        }

                    
                    if ( $k <= $images_array_count) { 

                            if(isset($_FILES["room_photos".$k]["name"]))
                            {
                                
                                foreach($_FILES["room_photos".$k]["error"] as $key=>$error) 
                                {
                                    $tmp_name = $_FILES["room_photos".$k]["tmp_name"][$key];

                                    $name = str_replace(' ', '_', $_FILES["room_photos".$k]["name"][$key]);
                                    
                                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                                    $name = time().'_'.$name;

                                    $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/images/multiple_rooms/'.$multiple_rooms[$k]->id;
                                                    
                                    if(!file_exists($filename))
                                    {
                                        mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/images/multiple_rooms/'.$multiple_rooms[$k]->id, 0777, true);
                                    }

                                    $fileSize = $_FILES["room_photos".$k]["size"][$key];
                                    $fileSizeKB = ($fileSize / 1024);
                                    $fileSizeMB = ($fileSizeKB / 1024);
                                    if($fileSizeMB > 10){
                                        $this->helper->flash_message('error', 'Maximum size for photo upload is 10 MB');
                                        return redirect(ADMIN_URL.'/rooms');
                                    }
                                                               
                                    if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif')   
                                    {            
                                    if(move_uploaded_file($tmp_name, "images/multiple_rooms/".$multiple_rooms[$k]->id."/".$name))
                                    {
                                        $this->helper->compress_image("images/multiple_rooms/".$multiple_rooms[$k]->id."/".$name, "images/multiple_rooms/".$multiple_rooms[$k]->id."/".$name, 80, 1440, 960);
                                        $this->helper->compress_image("images/multiple_rooms/".$multiple_rooms[$k]->id."/".$name, "images/multiple_rooms/".$multiple_rooms[$k]->id."/".$name, 80, 1349, 402);
                                        $this->helper->compress_image("images/multiple_rooms/".$multiple_rooms[$k]->id."/".$name, "images/multiple_rooms/".$multiple_rooms[$k]->id."/".$name, 80, 450, 250);
                                        $photos          = new MultipleRoomImages;
                                        $photos->multiple_room_id  = $multiple_rooms[$i]->id;
                                        $photos->room_id = $request->room_id;
                                        $photos->name    = $name;
                                        $photos->save();

                                        $multiple_rooms_steps_status = MultipleRoomsStepStatus::find($multiple_rooms[$i]->id);
                                        $multiple_rooms_steps_status->photos = 1;

                                        $multiple_rooms_steps_status->save();
                                    }
                                    }
                                }
                                $photos_featured = MultipleRoomImages::where('multiple_room_id',$multiple_rooms[$i]->id)->where('featured','Yes');
                                $photos_details = MultipleRoomImages::where('multiple_room_id',$multiple_rooms[$i]->id)->get();
                               
                                if($photos_featured->count() == 0 && count($photos_details) != 0)
                                {
                                    $photos = MultipleRoomImages::where('multiple_room_id',$multiple_rooms[$i]->id)->first();
                                    $photos->featured = 'Yes';
                                    $photos->save();
                                }

                            }
                        //}
                    }

                $sub_room = MultipleRooms::find($multiple_rooms[$i]->id);
                if($sub_room->steps_count==0){
                    $sub_room->status = "Listed";

                    $sub_room->save();
                }
                
                $k++;
            }
            $all_multi_room = MultipleRooms::where('room_id',$request->room_id)->pluck('id');
            $total_beds_count = RoomsBedType::whereIn('room_id',$all_multi_room)->sum('beds');
            $main_room =  Rooms::find($request->room_id);
            $main_room->beds = $total_beds_count;
            $main_room->save();

            $this->helper->flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect('admin/rooms');
        }
        else if($request->submit == 'terms')
        {
            $rooms = Rooms::find($request->room_id);
            
            $rooms->cancel_policy = $request->cancel_policy;

            $rooms->save();
            
            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'price_rules')
        {
            $length_of_stay_rules =  $request->length_of_stay ?: array();
            foreach($length_of_stay_rules as $rule) {
                if(@$rule['id']) {
                    $check = [
                        'id' => $rule['id'],
                        'room_id' => $request->room_id,
                        'type'    => 'length_of_stay',
                    ];
                }
                else {
                    $check = [
                        'room_id' => $request->room_id,
                        'type'    => 'length_of_stay',
                        'period'  => $rule['period']
                    ];
                }
                $price_rule = RoomsPriceRules::firstOrNew($check);
                $price_rule->room_id = $request->room_id;
                $price_rule->type =  'length_of_stay';
                $price_rule->period = $rule['period'];
                $price_rule->discount = $rule['discount'];

                $price_rule->save();
            }

            $early_bird_rules = $request->early_bird ?: array();
            foreach($early_bird_rules as $rule) {
                if(@$rule['id']) {
                    $check = [
                        'id' => $rule['id'],
                        'room_id' => $request->room_id,
                        'type'    => 'early_bird',
                    ];
                }
                else {
                    $check = [
                        'room_id' => $request->room_id,
                        'type'    => 'early_bird',
                        'period'  => $rule['period']
                    ];
                }
                $price_rule = RoomsPriceRules::firstOrNew($check);
                $price_rule->room_id = $request->room_id;
                $price_rule->type =  'early_bird';
                $price_rule->period = $rule['period'];
                $price_rule->discount = $rule['discount'];

                $price_rule->save();
            }

            $last_min_rules = $request->last_min ?: array();
            foreach($last_min_rules as $rule) {
                if(@$rule['id']) {
                    $check = [
                        'id' => $rule['id'],
                        'room_id' => $request->room_id,
                        'type'    => 'last_min',
                    ];
                }
                else {
                    $check = [
                        'room_id' => $request->room_id,
                        'type'    => 'last_min',
                        'period'  => $rule['period']
                    ];
                }
                $price_rule = RoomsPriceRules::firstOrNew($check);
                $price_rule->room_id = $request->room_id;
                $price_rule->type =  'last_min';
                $price_rule->period = $rule['period'];
                $price_rule->discount = $rule['discount'];

                $price_rule->save();
            }

            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'availability_rules') 
        {
            $availability_rules = $request->availability_rules ?: array();
            foreach($availability_rules as $rule) {
                if(@$rule['edit'] == 'true')
                {
                    continue;
                }
                $check = [
                    'id' => @$rule['id'] ?: '',
                ];
                $availability_rule = RoomsAvailabilityRules::firstOrNew($check);
                $availability_rule->room_id = $request->room_id;
                $availability_rule->start_date = date('Y-m-d', $this->helper->custom_strtotime(@$rule['start_date'], PHP_DATE_FORMAT));
                $availability_rule->end_date = date('Y-m-d', $this->helper->custom_strtotime(@$rule['end_date'], PHP_DATE_FORMAT));
                $availability_rule->minimum_stay = @$rule['minimum_stay'] ?: null;
                $availability_rule->maximum_stay = @$rule['maximum_stay'] ?: null;
                $availability_rule->type = @$rule['type'] != 'prev' ? @$rule['type']: @$availability_rule->type;
                $availability_rule->save();
            }
            $rooms_price = RoomsPrice::find($request->room_id);
            $rooms_price->minimum_stay = $request->minimum_stay ?: null;
            $rooms_price->maximum_stay = $request->maximum_stay ?: null;
            $rooms_price->save();

            flash_message('success', 'Room Updated Successfully'); // Call flash message function
            return redirect(ADMIN_URL.'/rooms');
        }
        else if($request->submit == 'cancel')
        {
            return redirect(ADMIN_URL.'/rooms');
        }
        else
        {
            return redirect(ADMIN_URL.'/rooms');
        }
    }

    public function roomStatusUpdate($room_id){
        $room = Rooms::where('id', $room_id)->first();
        $user = User::find($room->user_id);
        // if ($user->status == 'Active' && $room->steps_count == 0 && $room->verified=='Pending') {
        //     $room->status = 'Listed';
        //     $room->verified = 'Approved';
        //     $room->save();
        // }
    }

    public function delete_price_rule(Request $request) {
        $id = $request->id;
        if($request->type=='sub_room'){
            MultipleRoomsPriceRules::where('id', $id)->delete();
        }else{
            RoomsPriceRules::where('id', $id)->delete();
        }
        return json_encode(['success' => true]);
    }
    public function delete_availability_rule(Request $request) {
        $id = $request->id;
        if($request->type){
            MultipleRoomsAvailabilityRules::where('id', $id)->delete();
        }else{
            RoomsAvailabilityRules::where('id', $id)->delete();    
        }
        //RoomsAvailabilityRules::where('id', $id)->delete();
        return json_encode(['success' => true]);
    }
    

 public function update_video(Request $request)
     {
            
            $data_calendar     = @json_decode($request['data']);
            $rooms = Rooms::find($data_calendar->id);
             
            $search     = '#(.*?)(?:href="https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch?.*?v=))([\w\-]{10,12}).*#x';
                $count      = preg_match($search, $data_calendar->video);
                $rooms      = Rooms::find($data_calendar->id); 
                if($count == 1) {
                    $replace    = 'http://www.youtube.com/embed/$2';
                    $video      = preg_replace($search,$replace,$data_calendar->video);
                    $rooms->video = $video;
                }
                else {
                    $rooms->video = $data_calendar->video;
                }

            $rooms->save();
          
            return json_encode(['success'=>'true', 'steps_count' => $rooms->steps_count,'video' => $rooms->video]);
        }
    /**
     * Delete Rooms
     *
     * @param array $request    Input values
     * @return redirect     to Rooms View
     */
    public function latlong($address){
        $url = "http://maps.google.com/maps/api/geocode/json?address=".urlencode($address);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
            $responseJson = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($responseJson);

            if ($response->status == 'OK') {
                $latitude = $response->results[0]->geometry->location->lat;
                $longitude = $response->results[0]->geometry->location->lng;
                $add=array('lat'=>$latitude,'long'=>$longitude);
                return $add;
            }
            
    }


    





    public function multiple_delete_availability_rule(Request $request) {
        $id = $request->id;
        MultipleRoomsAvailabilityRules::where('id', $id)->delete();
        return json_encode(['success' => true]);
    }
    
    public function delete(Request $request)
    {
        $check = Reservation::whereRoomId($request->id)->count();

        if($check) {
            flash_message('error', 'This room has some reservations. So, you cannot delete this room.'); // Call flash message function
        }
        else 
        {   $exists_rnot = Rooms::find($request->id);
            if(@$exists_rnot){
            Rooms::find($request->id)->Delete_All_Room_Relationship(); 
           flash_message('success', 'Deleted Successfully');
           }
           else{
              flash_message('error', 'This Room Already Deleted.');
           } // Call flash message function
        }
        
        return redirect(ADMIN_URL.'/rooms');
    }

    /**
     * Users List for assign Rooms Owner
     *
     * @param array $request    Input values
     * @return json Users table
     */
    public function users_list(Request $request)
    {
        return User::where('first_name', 'like', $request->term.'%')->select('first_name as value','id')->get();
    }

    /**
     * Ajax function of Calendar Dropdown and Arrow
     *
     * @param array $request    Input values
     * @param array $calendar   Instance of CalendarController
     * @return html Calendar
     */
    public function ajax_calendar(Request $request, CalendarController $calendar)
    {
        $month             = $request->month;
        $year              = $request->year;
        $type              = @$request->type;

         if($type == 'sub_room'){
            $data['calendar']  = str_replace(['<form name="calendar-edit-form">','</form>', url('manage-listing/'.$request->id.'/calendar')], ['', '', 'javascript:void(0);'],$calendar->generate('sub_room',$request->id, $year, $month));
        }else{
            $data['calendar']  = str_replace(['<form name="calendar-edit-form">','</form>', url('manage-listing/'.$request->id.'/calendar')], ['', '', 'javascript:void(0);'],$calendar->generate('main_room',$request->id, $year, $month));
        }

        //$data['calendar']  = $calendar->generate($request->id, $year, $month);

        return $data['calendar'];
    }

    public function ajax_calendar_admin(Request $request, CalendarController $calendar)
    {
        $data_calendar     = @json_decode($request['data']);
        $year              = @$data_calendar->year;
        $month             = @$data_calendar->month;
        $type              = @$data_calendar->type;
        if($type == 'sub_room'){
            $data['calendar']  = str_replace(['<form name="calendar-edit-form">','</form>', url('manage-listing/'.$request->id.'/calendar')], ['', '', 'javascript:void(0);'],$calendar->generate('sub_room',$request->id, $year, $month));
        }else{
            $data['calendar']  = str_replace(['<form name="calendar-edit-form">','</form>', url('manage-listing/'.$request->id.'/calendar')], ['', '', 'javascript:void(0);'],$calendar->generate('main_room',$request->id, $year, $month));
        }

        return $data['calendar'];
    }

    /**
     * Delete Rooms Photo
     *
     * @param array $request    Input values
     * @return json success   
     */

    public function delete_mulitple_room(Request $request)
    {
        

        /*$check = MultipleReservation::whereMultipleRoomId($request->id)->count();

        $check1 = MultipleSpecialOffer::whereMultipleRoomId($request->id)->count();

        if(!$check && !$check1){*/
            $get_room=DB::table('multiple_rooms')->where('id',$request->id)->first();
            $id=$get_room->id;

            $room_count = DB::table('multiple_rooms')->where('room_id',$get_room->room_id)->count();
            if($room_count>1){
                $room_id=$get_room->room_id;
                $get_images=DB::table('multiple_room_images')->where('multiple_room_id',$id)->where('room_id',$room_id)->get();
                if($get_images != []){
                    foreach($get_images as $img)
                    {
                        DB::table('multiple_room_images')->where('id',$img->id)->delete();
                    }
                }
                
            DB::table('multiple_rooms_availability_rules')->where('multiple_room_id',$id)->delete();
                DB::table('rooms_bed_type')->where('room_id',$id)->where('type','Multiple')->delete();
                DB::table('multiple_rooms_price_rules')->where('multiple_room_id',$id)->delete();
                $stepstatus = DB::table('multiple_rooms_steps_status')->where('id',$id)->delete();
                $get_room=DB::table('multiple_rooms')->where('id',$request->id)->delete();
                echo "success"; 
                exit;
            }
           /* else{
                echo "error"; 
                exit;
            }
        }
        else{
            echo "error1"; 
            exit;
        }*/
        
    }
 public function delete_mulitple_room_bed_type(Request $request){
        
        $id = $request->id;

        if($request->room_id){
    $rooms_bed_type = RoomsBedType::where(['room_id'=>$request->room_id,'type'=>'Multiple'])->first();
            
            if($rooms_bed_type){
                RoomsBedType::where('id', $id)->delete();

                $total_beds_count = RoomsBedType::whereIn('room_id',explode(",",$request->room_id))->sum('beds');
               
                $rooms = MultipleRooms::find($request->room_id);
                $rooms->beds = $total_beds_count;
                $rooms->save();
              
                return json_encode(['success' => true,'delete_error'=>'false']);
            }
            return json_encode(['success' => true,'delete_error'=>'true']);
        }
    }
    public function multiple_delete_price_rule(Request $request) {
        $id = $request->id;
        MultipleRoomsPriceRules::where('id', $id)->delete();
        return json_encode(['success' => true]);
    }
    

    public function delete_multiple_photos(Request $request)
    {
        $photos          = DB::table('multiple_room_images')->where('id',$request->photo_id)->first();

        $room_id = $photos->room_id;
        
       DB::table('multiple_room_images')->where('id',$request->photo_id)->delete();
        
        $photos_featured = DB::table('multiple_room_images')->where(['multiple_room_id'=>$request->multiple_room_id,'room_id'=>$room_id])->where('featured','Yes');
            
        if($photos_featured->count() == 0)
        {
            $photos_featured = DB::table('multiple_room_images')->where(['multiple_room_id'=>$request->multiple_room_id,'room_id'=>$room_id]);
            
            if($photos_featured->count() != 0)
            {
                $photos1 = MultipleRoomImages::where('multiple_room_id',$request->multiple_room_id)->first();
                $photos1->featured = 'Yes';
                $photos1->save();
            }
        }
        
        return json_encode(['success'=>'true']);
    }
    
    public function delete_photo(Request $request)
    {
        
        $photos          = RoomsPhotos::find($request->photo_id);
        if($photos != NULL){
            /*delete file from server*/
                $compress_images = ['_450x250.','_1440x960.','_1349x402.'];
                $this->helper->remove_image_file($photos->original_name,"images/rooms/".$request->room_id,$compress_images);
            /*delete file from server*/
            $photos->delete();
        }
        
        // $photos_featured = RoomsPhotos::where('room_id',$request->room_id)->where('featured','Yes');            
        // if($photos_featured->count() == 0){
        //     $photos_featured = RoomsPhotos::where('room_id',$request->room_id);
        //     if($photos_featured->count() !=0){
        //         $photos = RoomsPhotos::where('room_id',$request->room_id)->first();
        //         $photos->featured = 'Yes';
        //         $photos->save();
        //     }
        // }
        
        return json_encode(['success'=>'true']);
    }

    /**
     * Ajax List Your Space Photos Highlights
     *
     * @param array $request    Input values
     * @return json success
     */
    public function photo_highlights(Request $request)
    {
        $photos = RoomsPhotos::find($request->photo_id);

        $photos->highlights = $request->data;

        $photos->save();

        return json_encode(['success'=>'true']);
    }

    public function popular(Request $request)
    {
        $prev = Rooms::find($request->id)->popular;

        if($prev == 'No'){
            $room = Rooms::find($request->id);
            $user_check = User::find($room->user_id);
            if($room->status != 'Listed')
            {
                flash_message('error', 'Not able to popular for unlisted listing');
                return back();
            }
            if($user_check->status != 'Active')
            {
                flash_message('error', 'Not able to popular for Not Active users');
                return back();
            }
        }

        if($prev == 'Yes')
            Rooms::where('id',$request->id)->update(['popular'=>'No']);
        else
            Rooms::where('id',$request->id)->update(['popular'=>'Yes']);

        flash_message('success', 'Updated Successfully'); // Call flash message function
        return redirect(ADMIN_URL.'/rooms');
    }

    public function recommended(Request $request)
    {
        $room = Rooms::find($request->id);
        $user_check = User::find($room->user_id);         
        if($room->status != 'Listed')
        {
            flash_message('error', 'Not able to recommend for unlisted listing');
            return back();
        }
        if($user_check->status != 'Active')
        {
            flash_message('error', 'Not able to recommend for Not Active users');
            return back();
        }

        $prev = $room->recommended;

        if($prev == 'Yes')
            Rooms::where('id',$request->id)->update(['recommended'=>'No']);
        else
            Rooms::where('id',$request->id)->update(['recommended'=>'Yes']);

        flash_message('success', 'Updated Successfully'); // Call flash message function
        return redirect(ADMIN_URL.'/rooms');
    }

    public function featured_image(Request $request) 
    {

        RoomsPhotos::whereRoomId($request->id)->update(['featured' => 'No']);

        RoomsPhotos::whereId($request->photo_id)->update(['featured' => 'Yes']);

        return 'success';
    }

    /*Admin Verify Listing*/
    public function update_room_status(Request $request) {

        //dd($request->option);

        //dd($request->id);
        $room = Rooms::find($request->id);

        $user_check = User::find($room->user_id);

        //dd($room->user_id);
        
        if ($user_check->status != 'Active') {
            flash_message('error', 'Not able to ' . $request->type . ' for Not Active users');
            return back();
        }

        if ($room->status == 'Unlisted') {
            flash_message('error', 'Not able to ' . $request->type . ' for unlisted listing');
            return back();
        }

        if($request->option == 'Approved'){
            
            Rooms::where('id', $request->id)->update(['status' => 'Listed']);
            
            //send admin approved email to host
            $email_controller = new EmailController;
            $email_controller->listing_approved_by_admin($request->id);

            //$email_controller->admin_approve_email($request->id);
            //$email_controller->admin_approve_email_host($request->id);
        }elseif($request->option == 'Pending' && $request->type == "verified"){
            Rooms::where('id', $request->id)->update(['status'=>'Pending']);
        }

        
        Rooms::where('id', $request->id)->update([$request->type => $request->option]);
        flash_message('success', 'Updated Successfully'); // Call flash message function

        return redirect(ADMIN_URL . '/rooms');
    }

    /**
    * Resubmit Listing in admin 
    */
    public function resubmit_listing(Request $request){

        $rooms = Rooms::find($request->room_id);

        $user_check = User::find($rooms->user_id);
        
        if ($user_check->status != 'Active') {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('message', 'Not able to verified for Not Active users');
            return "true";
        }

        if ($rooms->status == 'Unlisted') {
            
            Session::flash('alert-class', 'alert-danger');
            Session::flash('message', 'Not able to verified for unlisted listing');
            return "true";
        }
        $rooms->verified = 'Resubmit';
        $rooms->status = 'Resubmit';
        $rooms->save();

        $room_detail = Rooms::find($request->room_id);
        $message = new Messages;
        $message->room_id = $request->room_id;
        $message->reservation_id = $request->room_id.''.$room_detail->user_id; // $request->room_id;
        $message->user_from = $room_detail->user_id;
        $message->user_to   = $room_detail->user_id;
        $message->message   = $request->msg;
        $message->message_type = 13;
        $message->save();

        $admin_message = Messages::with('reservation')->where('id',$message->id)->get();
        $instant_message = $this->payment_helper->InstantMessage($admin_message);
        $result['instant_message'] = $instant_message[0];

        $guest_count =  Messages::where('user_to', $room_detail->user_id)->where('read', '0')->where('archive','0')->groupby('reservation_id')->get()->count();
        $guest = array('guest_id'=>$room_detail->user_id,'guest_count'=>$guest_count);
        $host = array('host_id'=>$room_detail->user_id,'host_count'=>$guest_count);
        $result['count'] = array_merge($guest,$host);
        $result['type']  = 'add';
        $result['inbox'] ='yes';
        $redis = \LRedis::connection();
        $redis->publish('chat', json_encode($result));

        $userDetails = User::find($room_detail->user_id);
        $user_data =array(
            'device_id'  => $userDetails->device_id,
            'device_type' => $userDetails->device_type,
        );
        $notification_data = array(
            'key'            => 'Chat',
            'type'           => 'Host',
            'message'        => $request->msg,        
            'title'          => 'Admin Resubmit Your Listing',
            'reservation_id' => (string) $room_detail->id,
            'host_user_id'   => (string) $room_detail->user_id,
        );
        $this->payment_helper->SendPushNotification($user_data,$notification_data);

        //$email_controller = new EmailController;
        //$email_controller->admin_resubmit_email($request->room_id,$request->msg);
        Session::flash('alert-class', 'alert-success');
        Session::flash('message', 'Resubmited Successfully');
        return "true";
    }
}
