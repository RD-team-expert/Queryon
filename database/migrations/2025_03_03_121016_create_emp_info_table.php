<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEMPInfoTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('EMP_Info', function (Blueprint $table) {
            $table->id(); // ID (auto-increment primary key)
            $table->string('First_Name_English');
            $table->string('Last_Name_English');
            $table->string('First_And_Last_Name_English');
            $table->string('First_Name_Arabic');
            $table->string('Last_Name_Arabic');
            $table->string('First_And_Last_Name_Arabic');
            $table->date('HiringDate');
            $table->string('PneEmail');
            $table->string('PersonalEmail');
            $table->string('SYPhone');
            $table->string('USPhone');
            $table->string('YourPicture'); // Stores the path or filename of the image
            $table->longText('AboutYou'); // For longer text content
            $table->string('Password'); // Make sure to hash the password when saving!
            $table->string('Shift');
            $table->string('Depatment_Name');
            $table->string('Position_Name');
            $table->boolean('Offboarded')->default(false); // Assuming false = No, true = Yes
            $table->integer('Level');
            $table->integer('Tier');
            $table->integer('Entry_Number');
            $table->timestamps(); // created_at and updated_at fields
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('EMP_Info');
    }
}
