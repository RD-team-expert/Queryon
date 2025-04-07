<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClockInOutIdToEmployeesDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Employees_Data', function (Blueprint $table) {
            $table->bigInteger('CLockInOutID')->nullable()->after('us_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Employees_Data', function (Blueprint $table) {
            $table->dropColumn('CLockInOutID');
        });
    }
}