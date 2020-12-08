<?php

/**
 * Rooms Description Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Rooms Description
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;

class RoomsDescription extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rooms_description';

    public $timestamps = false;

    protected $primaryKey = 'room_id';
    protected $fillable = ['space', 'access','interaction','notes','house_rules'];
    protected $appends = ['ori_space','ori_house_rules','ori_transit','ori_access','ori_interaction','ori_notes','ori_neighborhood_overview'];
/*    public function setSpaceAttribute($input){
         $this->attributes['space'] = strip_tags($input);
    }
    public function setAccessAttribute($input){
         $this->attributes['access'] = strip_tags($input);
    }
    public function setInteractionAttribute($input){
         $this->attributes['interaction'] = strip_tags($input);
    }
    public function setNotesAttribute($input){
         $this->attributes['notes'] = strip_tags($input);
    }*/
   /* public function setHouseRulesAttribute($input){
         $this->attributes['house_rules'] = strip_tags($input);
    }*/

        public function getOriSpaceAttribute(){
          return $this->attributes['space'];
        }
          public function getOriHouseRulesAttribute()
       {
        return $this->attributes['house_rules'];
        }
         public function getOriTransitAttribute()
       {
    return $this->attributes['transit'];
        }
        public function getOriAccessAttribute()
       {
        return $this->attributes['access'];
        }
        public function getOriInteractionAttribute()
      {
            return $this->attributes['interaction'];
        }
         public function getOriNotesAttribute()
        {
      return $this->attributes['notes'];
        }
        public function getOriNeighborhoodOverviewAttribute()
      {
         return $this->attributes['neighborhood_overview'];
        }
        public function getSpaceAttribute()
   {
       $default_lang = Language::where('default_language',1)->first()->value;

       $lang = Language::whereValue((Session::get('language')) ? Session::get('language') : $default_lang)->first()->value;
        
       if($lang == 'en')
           return $this->attributes['space'];
       else {
           $name = @RoomsDescriptionLang::where('room_id', $this->attributes['room_id'])->where('lang_code', $lang)->first()->space;
           if($name)
               return $name;
           else
               return $this->attributes['space'];
       }
   } 
    public function getHouseRulesAttribute()
   {
       $default_lang = Language::where('default_language',1)->first()->value;

       $lang = Language::whereValue((Session::get('language')) ? Session::get('language') : $default_lang)->first()->value;
        
       if($lang == 'en')
           return $this->attributes['house_rules'];
       else {
           $name = @RoomsDescriptionLang::where('room_id', $this->attributes['room_id'])->where('lang_code', $lang)->first()->house_rules;
           if($name)
               return $name;
           else
               return $this->attributes['house_rules'];
       }
   }
    public function getTransitAttribute()
   {
       $default_lang = Language::where('default_language',1)->first()->value;

       $lang = Language::whereValue((Session::get('language')) ? Session::get('language') : $default_lang)->first()->value;
        
       if($lang == 'en')
           return $this->attributes['transit'];
       else {
           $name = @RoomsDescriptionLang::where('room_id', $this->attributes['room_id'])->where('lang_code', $lang)->first()->transit;
           if($name)
               return $name;
           else
               return $this->attributes['transit'];
       }
   }
   public function getAccessAttribute()
   {
       $default_lang = Language::where('default_language',1)->first()->value;

       $lang = Language::whereValue((Session::get('language')) ? Session::get('language') : $default_lang)->first()->value;

       if($lang == 'en')
           return $this->attributes['access'];
       else {
           $name = @RoomsDescriptionLang::where('room_id', $this->attributes['room_id'])->where('lang_code', $lang)->first()->access;
           if($name)
               return $name;
           else
               return $this->attributes['access'];
       }
   }
   public function getInteractionAttribute()
   {
       $default_lang = Language::where('default_language',1)->first()->value;

       $lang = Language::whereValue((Session::get('language')) ? Session::get('language') : $default_lang)->first()->value;

       if($lang == 'en')
           return $this->attributes['interaction'];
       else {
           $name = @RoomsDescriptionLang::where('room_id', $this->attributes['room_id'])->where('lang_code', $lang)->first()->interaction;
           if($name)
               return $name;
           else
               return $this->attributes['interaction'];
       }
   } 
   public function getNotesAttribute()
   {
       $default_lang = Language::where('default_language',1)->first()->value;

       $lang = Language::whereValue((Session::get('language')) ? Session::get('language') : $default_lang)->first()->value;

       if($lang == 'en')
           return $this->attributes['notes'];
       else {
           $name = @RoomsDescriptionLang::where('room_id', $this->attributes['room_id'])->where('lang_code', $lang)->first()->notes;
           if($name)
               return $name;
           else
               return $this->attributes['notes'];
       }
   }
    public function getNeighborhoodOverviewAttribute()
   {
       $default_lang = Language::where('default_language',1)->first()->value;

       $lang = Language::whereValue((Session::get('language')) ? Session::get('language') : $default_lang)->first()->value;

       if($lang == 'en')
           return $this->attributes['neighborhood_overview'];
       else {
           $name = @RoomsDescriptionLang::where('room_id', $this->attributes['room_id'])->where('lang_code', $lang)->first()->neighborhood_overview;
           if($name)
               return $name;
           else
               return $this->attributes['neighborhood_overview'];
       }
   }

}
