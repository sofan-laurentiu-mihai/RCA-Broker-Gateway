<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Allows the connections one to many between users and policies
 * The field is nullable to allow the issue of policies to unregistered users too
 */
return new class extends Migration
{

    public function up(): void
    {
        /**
         * We create a foreign key to the users table
         * nullOnDelete() assures the referential integrity, if the user was deleted
         * At the same time, we respect the ASF laws, saving the audit log, setting the user_id to null
         */
        Schema::table('rca_policies', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
        });
    }

    /**
     * We apply the rollback.
     */
    public function down(): void
    {
        Schema::table('rca_policies', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
