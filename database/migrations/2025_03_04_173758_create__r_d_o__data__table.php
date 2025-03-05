<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRDODataTable  extends Migration
{
    public function up()
    {
        Schema::create('rdo_data_table', function (Blueprint $table) {
            $table->id();
            $table->string('Name_Lable');
            $table->string('Name_ID');
            $table->date('HookTodaysDate');
            $table->text('HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA');
            $table->date('HookStartDate');
            $table->date('HookEndDate');
            $table->string('HookDepartment_ID');
            $table->string('HookDepartment_Lable');
            $table->integer('HookHowManyDaysDoYouNeed2');
            $table->string('HookType');
            $table->string('HookAreYouAbleToProvideAProof');
            $table->string('HookHowManyDaysDoYouNeed2_IncrementBy');
            $table->string('HookAreYouAbleToProvideMoreSpecificPlease_IsRequired');
            $table->string('DirectManagerName_ID');
            $table->string('DirectManagerName_Lable');
            $table->string('HookApprove')->nullable();
            $table->string('Note')->nullable();
            $table->string('AdminLink');
            $table->string('Status');
            $table->string('PublicLink');
            $table->string('InternalLink');
            $table->string('Entry_Number');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rdo_data_table');
    }
}
