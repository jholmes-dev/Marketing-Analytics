<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->ulid('id')->unique();
            $table->timestamps();
            $table->integer('property_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('exp_date')->nullable();
            $table->integer('total_users')->nullable();
            $table->integer('sessions')->nullable();
            $table->integer('page_views')->nullable();
            $table->float('engagement_rate', 8, 4)->nullable();
            $table->float('events_per_session', 8, 4)->nullable();
            $table->float('sessions_per_user', 8, 4)->nullable();
            $table->text('date_session')->nullable();
            $table->text('browsers')->nullable();
            $table->text('devices')->nullable();
            $table->text('channels')->nullable();
            $table->text('pages')->nullable();
            $table->text('cities')->nullable();
            $table->text('queries')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
