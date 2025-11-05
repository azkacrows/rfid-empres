<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jadwal_sholat', function (Blueprint $table) {
            // Tracking sumber data
            $table->boolean('is_manual')->default(false)->after('isya');
            $table->timestamp('last_synced_at')->nullable()->after('is_manual');
            $table->unsignedBigInteger('edited_by')->nullable()->after('last_synced_at');
            
            // Foreign key ke users
            $table->foreign('edited_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('jadwal_sholat', function (Blueprint $table) {
            $table->dropForeign(['edited_by']);
            $table->dropColumn(['is_manual', 'last_synced_at', 'edited_by']);
        });
    }
};