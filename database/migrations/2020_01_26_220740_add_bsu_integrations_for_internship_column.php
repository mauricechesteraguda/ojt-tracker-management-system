<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBsuIntegrationsForInternshipColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->string('schoolyear')->nullable();
            $table->string('course_code')->nullable();
            $table->string('end_date')->nullable();
            $table->string('semester')->nullable();
            $table->string('campus')->nullable();
            $table->string('college_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn(['schoolyear']);
            $table->dropColumn(['course_code']);
            $table->dropColumn(['end_date']);
            $table->dropColumn(['semester']);
            $table->dropColumn(['campus']);
            $table->dropColumn(['college_code']);
        });
    }
}
