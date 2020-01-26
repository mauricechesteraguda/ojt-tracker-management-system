<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBsuIntegrationsForUserColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contact_no')->nullable();
            $table->string('parent')->nullable();
            $table->string('parent_contact_no')->nullable();
            $table->string('current_schoolyear')->nullable();
            $table->string('current_course_code')->nullable();
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
            $table->dropColumn(['contact_no']);
            $table->dropColumn(['parent']);
            $table->dropColumn(['parent_contact_no']);
            $table->dropColumn(['current_schoolyear']);
            $table->dropColumn(['current_course_code']);

        });
    }
}
