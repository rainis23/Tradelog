<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Creates the "trades" table.
// Every trade belongs to one account. If the account is deleted, its trades are deleted too (cascade).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade'); // accounts.id
            $table->string('instrument');               // NQ, MNQ, ES, MES or GC
            $table->string('direction');                // "long" (buy) or "short" (sell)
            $table->integer('quantity');                // number of contracts
            $table->decimal('entry_price', 12, 2);
            $table->decimal('exit_price', 12, 2);
            $table->decimal('pnl', 12, 2);              // profit or loss in $, calculated by Trade::calculatePnl()
            $table->date('trade_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
