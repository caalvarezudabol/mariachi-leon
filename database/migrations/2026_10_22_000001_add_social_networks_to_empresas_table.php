<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('redes_facebook', 500)->nullable()->after('redes_linktree');
            $table->string('redes_instagram', 500)->nullable()->after('redes_facebook');
            $table->string('redes_tiktok', 500)->nullable()->after('redes_instagram');
            $table->string('redes_youtube', 500)->nullable()->after('redes_tiktok');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'redes_facebook',
                'redes_instagram',
                'redes_tiktok',
                'redes_youtube',
            ]);
        });
    }
};
