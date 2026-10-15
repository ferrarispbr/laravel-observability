<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
			$table->string('address')->nullable();
			$table->string('address_number', 20)->nullable();
			$table->string('address_complement')->nullable();
			$table->string('neighborhood')->nullable();
			$table->string('city')->nullable();
			$table->string('state', 2)->nullable();
			$table->string('zip_code', 9)->nullable();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
			$table->dropColumn([
				'address',
				'address_number',
				'address_complement',
				'neighborhood',
				'city',
				'state',
				'zip_code',
			]);
        });
    }
};
