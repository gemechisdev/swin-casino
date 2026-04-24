<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Lottery Campaigns
        Schema::create('lottery_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('ticket_price', 28, 8)->default(0);
            $table->integer('max_tickets_per_user')->default(0);
            $table->integer('total_ticket_limit')->default(0);
            $table->integer('serial_length')->default(10);
            $table->tinyInteger('draw_mode')->default(1)->comment('1: From Sold, 2: From Space');
            $table->tinyInteger('auto_next_phase')->default(1);
            $table->integer('phase_duration_days')->default(7);
            $table->decimal('house_edge', 5, 2)->default(10.00);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 2. Lottery Phases
        Schema::create('lottery_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_campaign_id')->constrained('lottery_campaigns')->onDelete('cascade');
            $table->integer('phase_number');
            $table->dateTime('sale_start_at');
            $table->dateTime('sale_end_at');
            $table->dateTime('draw_at');
            $table->dateTime('drawn_at')->nullable();
            $table->integer('tickets_sold')->default(0);
            $table->decimal('total_revenue', 28, 8)->default(0);
            $table->decimal('prize_pool', 28, 8)->default(0);
            $table->decimal('house_cut', 28, 8)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });

        // 3. Lottery Prize Tiers
        Schema::create('lottery_prize_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_phase_id')->constrained('lottery_phases')->onDelete('cascade');
            $table->string('prize_title');
            $table->tinyInteger('prize_type')->default(1)->comment('1: Cash, 2: Physical');
            $table->tinyInteger('amount_mode')->default(1)->comment('1: Fixed, 2: Pot Share');
            $table->decimal('prize_amount', 28, 8)->default(0);
            $table->decimal('pot_percent', 5, 2)->default(0.00);
            $table->integer('winner_count')->default(1);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        // 4. Lottery Tickets
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_campaign_id')->constrained('lottery_campaigns')->onDelete('cascade');
            $table->foreignId('lottery_phase_id')->constrained('lottery_phases')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('serial', 40);
            $table->decimal('purchase_price', 28, 8)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 5. Lottery Winners
        Schema::create('lottery_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_phase_id')->constrained('lottery_phases')->onDelete('cascade');
            $table->foreignId('lottery_ticket_id')->constrained('lottery_tickets')->onDelete('cascade');
            $table->foreignId('lottery_prize_tier_id')->constrained('lottery_prize_tiers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('prize_type')->default(1);
            $table->decimal('prize_amount', 28, 8)->default(0);
            $table->tinyInteger('is_distributed')->default(0);
            $table->dateTime('distributed_at')->nullable();
            $table->tinyInteger('delivery_status')->nullable();
            $table->text('admin_note')->nullable();
            $table->tinyInteger('is_archived')->default(0);
            $table->timestamps();
        });

        // 6. Lottery Draw Archives
        Schema::create('lottery_draw_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_phase_id')->constrained('lottery_phases')->onDelete('cascade');
            $table->json('draw_data');
            $table->dateTime('drawn_at');
            $table->timestamps();
        });

        // 7. Lottery Transactions
        Schema::create('lottery_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lottery_phase_id')->constrained('lottery_phases')->onDelete('cascade');
            $table->foreignId('lottery_ticket_id')->nullable()->constrained('lottery_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 28, 8)->default(0);
            $table->decimal('post_balance', 28, 8)->default(0);
            $table->string('trx', 40);
            $table->tinyInteger('type')->comment('1: Purchase, 2: Refund, 3: Win');
            $table->timestamps();
        });

        // Seed Cron Job
        DB::table('cron_jobs')->insert([
            'name'            => 'Lottery Draw Engine',
            'alias'           => 'lottery_draw',
            'action'          => '["App\\\\Http\\\\Controllers\\\\Admin\\\\LotteryCronController", "run"]',
            'cron_schedule_id' => 1,
            'next_run'        => now(),
            'last_run'        => now(),
            'is_running'      => 1,
            'is_default'      => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lottery_transactions');
        Schema::dropIfExists('lottery_draw_archives');
        Schema::dropIfExists('lottery_winners');
        Schema::dropIfExists('lottery_tickets');
        Schema::dropIfExists('lottery_prize_tiers');
        Schema::dropIfExists('lottery_phases');
        Schema::dropIfExists('lottery_campaigns');
        
        DB::table('cron_jobs')->where('alias', 'lottery_draw')->delete();
    }
};
