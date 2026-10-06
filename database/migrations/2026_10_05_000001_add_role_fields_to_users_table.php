<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // organizers can log in with mobile too, so email becomes optional
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 15)->nullable()->unique()->after('email');
            $table->enum('role', ['superadmin', 'admin', 'organizer'])->default('organizer')->after('password');
            $table->string('department')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['mobile']);
            $table->dropColumn(['mobile', 'role', 'department', 'is_active']);
        });
    }
};
