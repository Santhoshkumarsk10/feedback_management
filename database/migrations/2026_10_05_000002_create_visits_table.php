<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Visitors do not log in, so their details live on the visit itself.
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('visitor_mobile', 15)->nullable()->index();
            $table->string('visitor_company')->nullable();
            $table->string('visitor_email')->nullable();
            $table->date('visit_date');
            $table->string('purpose')->nullable();
            $table->timestamps();

            $table->index(['organizer_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
