<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClusterAndDateVisitedColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->integer('cluster_id')->nullable();
            $table->string('date_visited')->nullable();
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
            $table->dropColumn(['cluster_id']);
            $table->dropColumn(['date_visited']);
        });
    }
}
