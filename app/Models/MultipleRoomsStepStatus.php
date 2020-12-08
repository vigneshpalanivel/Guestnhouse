<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultipleRoomsStepStatus extends Model
{
   	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'multiple_rooms_steps_status';

    public $timestamps = false;

    protected $primaryKey = 'id';

    public function setAttribute($attribute, $value)
    {
        
        $this->attributes[$attribute] = $value.'';
        
    }
}
