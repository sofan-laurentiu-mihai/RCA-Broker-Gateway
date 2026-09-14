<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the database table for finalized and issued RCA insurance policies.
     */
    public function up(): void
    {
        Schema::create('rca_policies', function (Blueprint $table) {
            $table->id();
            // Foreign key linking the policy to the selected quote record
            $table->foreignId('offer_id')->constrained('rca_offers')->onDelete('cascade');
            // Foreign key linking to the audit trail entry of the insurance request
            $table->foreignId('audit_log_id')->constrained('rca_audit_logs')->onDelete('cascade');
            $table->string('policy_number')->nullable();
            $table->string('policy_series')->nullable();
            $table->string('status')->default('issued');
            $table->string('document_url')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->boolean('has_direct_compensation')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drops the rca_policies table upon rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('rca_policies');
    }
};
