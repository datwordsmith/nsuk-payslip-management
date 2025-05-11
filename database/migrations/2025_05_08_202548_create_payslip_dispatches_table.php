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
        Schema::create('payslip_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('staff_id');
            $table->string('email');
            $table->integer('month');
            $table->integer('year');
            $table->string('status');
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payslip_dispatches');
    }
};
