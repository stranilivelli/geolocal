<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('province', 5)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country')->default('Italia');
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->integer('zoom')->default(13);
            $table->text('intro')->nullable();
            $table->text('full_text')->nullable();
            $table->text('hours_prices')->nullable();
            $table->string('image')->nullable();
            // convezione diretta o indiretta
            $table->enum('convention_type', ['diretta', 'indiretta'])->default('diretta');
            $table->boolean('published')->default(false);
            $table->boolean('featured')->default(false);
            $table->text('notes')->nullable();        // note interne, non visibili al pubblico
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['published', 'city']);
            $table->index(['lat', 'lng']);
        });

        // Tabella pivot location <-> category (N:N)
        Schema::create('category_location', function (Blueprint $table) {
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['location_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_location');
        Schema::dropIfExists('locations');
    }
};
