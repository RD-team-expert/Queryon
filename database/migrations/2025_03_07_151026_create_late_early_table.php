<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLateEarlyTable extends Migration
{
    public function up()
    {
        Schema::create('late_early', function (Blueprint $table) {
            $table->id();
            $table->string('HookDirectManagerName_ID')->nullable();
            $table->string('HookDirectManagerName_Lable')->nullable();
            $table->string('HookApprove')->nullable();
            $table->text('HookNote')->nullable();
            $table->string('HookName_ID')->nullable();
            $table->string('HookName_Lable')->nullable();
            $table->date('HookTodaysDate')->nullable();
            $table->string('HookPleaseProvideAReasonForYourRequest')->nullable();
            $table->string('HookWhoWillCoverYourShiftIfYouDontHaveYouCanWriteNA')->nullable();
            $table->string('HookComingLateLeavingEarlier')->nullable();
            $table->string('HookDepartment_ID')->nullable();
            $table->string('HookDepartment_Lable')->nullable();
            $table->time('HookComingHour')->nullable();
            $table->time('HookLeavingHour')->nullable();
            $table->string('HookShift2')->nullable();
            $table->string('HookChangeSift')->nullable();
            $table->time('HookStartAt')->nullable();
            $table->time('HookEndAt')->nullable();
            $table->text('AdminLink')->nullable();
            $table->string('DateCreated')->nullable();
            $table->string('DateSubmitted')->nullable();
            $table->string('DateUpdated')->nullable();
            $table->integer('EntryNumber')->nullable()->comment('Cognito_ID');
            $table->text('PublicLink')->nullable();
            $table->text('InternalLink')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('late_early');
    }
}
