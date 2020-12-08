<?php

/**
 * MultipleSpecialOffer Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    MultipleSpecialOffer
 * @author      Trioangle Product Team
 * @version     1.5.8.3
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;

class MultipleSpecialOffer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'multiple_special_offer';

    public $timestamps = false;

    public function multiple_rooms()
    {
      return $this->belongsTo('App\Models\MultipleRooms','multiple_room_id','id');
    }

}
