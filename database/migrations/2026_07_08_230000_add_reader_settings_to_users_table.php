<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_preference')->default('dark');
            $table->unsignedTinyInteger('reader_font_size')->default(18);
            $table->string('reader_font_family')->default('serif');
            $table->decimal('reader_line_height', 3, 2)->default(1.75);
            $table->unsignedSmallInteger('reader_content_width')->default(760);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'theme_preference',
                'reader_font_size',
                'reader_font_family',
                'reader_line_height',
                'reader_content_width',
            ]);
        });
    }
};
