<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * It creates database tables for API audit trails and received RCA quotation.
     */
    public function up(): void
    {
        // 1. Audit trail table tracking complete network interactions with external providers
        Schema::create('rca_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action');
            $table->json('user_input')->nullable();
            $table->json('provider_request')->nullable();
            $table->json('provider_response')->nullable();
            $table->integer('http_status')->nullable();
            $table->string('correlation_id')->nullable();
            $table->timestamps();
        });

        // 2. Storage table for individual insurance company offers parsed from the responses of the provider
        Schema::create('rca_offers', function (Blueprint $table) {
            $table->id();
            // Foreign key linking each offer back to the specific audit entry that produces it
            $table->foreignId('audit_log_id')->constrained('rca_audit_logs')->onDelete('cascade');
            $table->unsignedBigInteger('offer_id');
            $table->string('provider_offer_code');
            $table->string('insurer');
            $table->decimal('premium_amount', 10, 2);
            $table->decimal('direct_compensation_amount', 10, 2)->nullable();
            $table->string('bonus_malus', 5)->nullable();
            $table->string('pid_url')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drops chjild tables first to respect foreign key integrity constrains.
     */
    public function down(): void
    {
        Schema::dropIfExists('rca_offers');
        Schema::dropIfExists('rca_audit_logs');
    }
};
