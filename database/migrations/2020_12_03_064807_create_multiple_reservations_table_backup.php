<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMultipleReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('multiple_reservation');

        Schema::create('multiple_reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reservation_id')->unsigned();
            $table->foreign('reservation_id')->references('id')->on('reservation');
            $table->integer('multiple_room_id');
            $table->integer('number_of_guests');
            $table->integer('number_of_rooms');
            $table->integer('nights');
            $table->integer('per_night');
            $table->integer('subtotal');
            $table->integer('cleaning');
            $table->integer('additional_guest');
            $table->integer('security');
            $table->integer('service');
            $table->integer('host_fee');            
            $table->enum('host_penalty', ['0', '1'])->default('0');
            $table->integer('total');
            $table->integer('base_per_night');
            $table->enum('length_of_stay_type', ['weekly', 'monthly', 'custom'])->nullable();
            $table->integer('length_of_stay_discount');
            $table->integer('length_of_stay_discount_price');
            $table->enum('booked_period_type', ['early_bird', 'last_min'])->nullable();
            $table->integer('booked_period_discount');
            $table->integer('booked_period_discount_price');
            $table->string('currency_code',10);
            $table->foreign('currency_code')->references('code')->on('currency');
            $table->enum('status', ['Pending', 'Accepted', 'Declined', 'Expired', 'Checkin', 'Checkout', 'Completed', 'Cancelled','Pre-Accepted','Pre-Approved'])->nullable();
            $table->enum('type', ['contact', 'reservation'])->nullable();
            $table->integer('special_offer_id');              
            $table->timestamps();
        });
        $statement = "ALTER TABLE multiple_reservations AUTO_INCREMENT = 10001;";

        DB::unprepared($statement);        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('multiple_reservations');
    }
}
