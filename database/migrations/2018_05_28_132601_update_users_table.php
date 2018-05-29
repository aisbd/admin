<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fname')->nullable();
            $table->string('lname')->nullable();
            $table->string('sponsor_id')->nullable();
            $table->string('birth')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            $table->string('street_address')->nullable();
            $table->string('apartment')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fname');
            $table->dropColumn('lname');
            $table->dropColumn('sponsor_id');
            $table->dropColumn('birth');
            $table->dropColumn('company');
            $table->dropColumn('country');
            $table->dropColumn('street_address');
            $table->dropColumn('apartment');
            $table->dropColumn('city');
            $table->dropColumn('zip');
        });
    }
}
