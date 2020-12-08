<?php

/**
 * RoomsBedType Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    RoomsBedType
 * @author      Trioangle Product Team
 * @version     1.5.8.3
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;
use Request;

class RoomsBedType extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rooms_bed_type';

    protected $appends = ['bed_type_name'];

    protected $fillable = ['bed_type', 'beds'];


    // Get bed_type_name from bed_type table
    public function getBedTypeNameAttribute() {
        if ($this->attributes['bed_type'] != NULL) {
            return BedType::find($this->attributes['bed_type'])->name;
        } else {
            return $this->attributes['bed_type'];
        }

    }

    // Join with bed_type table
    public function bed_types() {
        return $this->belongsTo('App\Models\BedType', 'bed_type', 'id');
    }


    
}
