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
            $table->string('Name_Lable')->nullable();
            $table->string('Name_ID')->nullable();
            $table->date('HookTodaysDate')->nullable();
            $table->text('HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA')->nullable();
            $table->date('HookStartDate')->nullable();
            $table->date('HookEndDate')->nullable();
            $table->string('HookDepartment_ID')->nullable();
            $table->string('HookDepartment_Lable')->nullable();
            $table->integer('HookHowManyDaysDoYouNeed2')->nullable();
            $table->string('HookType')->nullable();
            $table->string('HookAreYouAbleToProvideAProof')->nullable();
            $table->string('HookHowManyDaysDoYouNeed2_IncrementBy')->nullable();
            $table->string('HookAreYouAbleToProvideMoreSpecificPlease_IsRequired')->nullable();
            $table->string('DirectManagerName_ID')->nullable();
            $table->string('DirectManagerName_Lable')->nullable();
            $table->string('HookApprove')->nullable();
            $table->string('Note')->nullable();
            $table->string('AdminLink')->nullable();
            $table->string('Status')->nullable();
            $table->string('PublicLink')->nullable();
            $table->string('InternalLink')->nullable();
            $table->string('Entry_Number')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rdo_data_table');
    }
}
