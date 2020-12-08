<?php

/**
 * MultipleRoomsAvailabilityRules Model
 *
 * @package     Makent
 * @subpackage  Model
 * @category    MultipleRoomsAvailabilityRules
 * @author      Trioangle Product Team
 * @version     1.5.8.3
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use App\Models\Currency;
use Session;

class MultipleRoomsAvailabilityRules extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'multiple_rooms_availability_rules';

    protected $appends = ['during', 'start_date_formatted', 'end_date_formatted'];

    protected $fillable = ['id'];

    public $timestamps = false;

    public function getStartDateFormattedAttribute() {
        $format = PHP_DATE_FORMAT;
        if(request()->segment(1) == 'api') {
            $format = 'd-m-Y';
        }
        return date($format, strtotime(@$this->attributes['start_date']));
    }
    public function getEndDateFormattedAttribute() {
        $format = PHP_DATE_FORMAT;
        if(request()->segment(1) == 'api') {
            $format = 'd-m-Y';
        }
        return date($format, strtotime(@$this->attributes['end_date']));
    }

    public function getDuringAttribute() {
    	$start_date = date('d M Y', strtotime(@$this->attributes['start_date']));
    	$end_date = date('d M Y', strtotime(@$this->attributes['end_date']));

    	$during = $start_date.' - '.$end_date;
    	return $during;
    }

}
