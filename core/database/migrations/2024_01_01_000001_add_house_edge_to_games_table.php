<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->decimal('house_edge', 8, 2)->default(5.00)->after('probable_win_demo');
            $table->decimal('house_edge_demo', 8, 2)->default(2.00)->after('house_edge');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['house_edge', 'house_edge_demo']);
        });
    }
};
