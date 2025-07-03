<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary(); // Assuming id is a string (e.g., UUID)
            $table->string('userPrincipalName')->unique();
            $table->string('displayName');
            $table->string('surname');
            $table->string('mail')->unique();
            $table->string('givenName');
            $table->string('userType')->nullable();
            $table->string('jobTitle')->nullable();
            $table->string('department')->nullable();
            $table->boolean('accountEnabled')->default(true);
            $table->string('usageLocation')->nullable();
            $table->string('streetAddress')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('officeLocation')->nullable();
            $table->string('city')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('telephone')->nullable();
            $table->string('mobilePhone')->nullable();
            $table->string('alternateEmailAddress')->nullable();
            $table->string('ageGroup')->nullable();
            $table->string('consentProvidedForMinor')->nullable();
            $table->string('legalAgeGroupClassification')->nullable();
            $table->string('companyName')->nullable();
            $table->string('creationType')->nullable();
            $table->boolean('directorySynced')->nullable();
            $table->string('invitationState')->nullable();
            $table->string('identityIssuer')->nullable();
            $table->timestamp('createdDateTime')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}