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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('client_name', 255)->nullable();
            $table->string('client_email', 255)->nullable();
            $table->boolean('batch_email');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->boolean('email_sent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('client_name');
            $table->dropColumn('client_email');
            $table->dropColumn('batch_email');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('email_sent');
        });
    }
};
