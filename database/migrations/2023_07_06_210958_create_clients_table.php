<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Ramsey\Uuid\v1;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->integer('dni');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('neighborhood');
            $table->string('address');
            $table->string('city');
            $table->string('quota');
            $table->string('available_points');
            $table->string('client_code');
            $table->string('phone');
            $table->string('campaign');
            $table->string('zip_code');
            $table->string('balance');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
