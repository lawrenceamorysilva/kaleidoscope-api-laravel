<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModifiedByAdminUserIdToDropshipOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('modified_by_admin_user_id')->nullable()->after('user_id');
            $table->foreign('modified_by_admin_user_id')->references('id')->on('admin_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropForeign(['modified_by_admin_user_id']);
            $table->dropColumn('modified_by_admin_user_id');
        });
    }

}
