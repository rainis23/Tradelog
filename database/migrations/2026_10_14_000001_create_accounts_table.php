<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the "accounts" table.
// One user can have many trading accounts.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // the owner (users.id)
            $table->string('name');                                           // e.g. "Apex 50K Evaluation"
            $table->string('broker')->nullable();                             // e.g. "Tradovate" (optional)
            $table->decimal('starting_balance', 12, 2);                       // e.g. 50000.00
            $table->timestamps();                                             // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
