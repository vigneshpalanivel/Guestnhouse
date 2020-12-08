<?php

/**
 * Rooms Beds
 *
 * @package     Makent
 * @subpackage  Model
 * @category    Rooms Beds
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;

class RoomsBeds extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rooms_beds';

    public $guarded = [];

    public $timestamps = false;

  /*  public function getIconAttribute()
    {
        $name = strtolower(str_replace(' ','-',$this->attributes['name']));
        $url = url('images/icons/bed_type/'.$name.'.png');
        return $url;

    }*/

  
}
