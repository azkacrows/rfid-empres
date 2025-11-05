<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jadwal_sholat', function (Blueprint $table) {
            $table->string('hari', 20)->nullable()->after('tanggal'); // Sabtu, Minggu, dll
        });
    }

    public function down()
    {
        Schema::table('jadwal_sholat', function (Blueprint $table) {
            $table->dropColumn('hari');
        });
    }
};