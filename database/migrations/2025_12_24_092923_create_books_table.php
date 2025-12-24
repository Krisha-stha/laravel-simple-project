<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('books', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->text('description')->nullable();
      $table->decimal('price', 8, 2);
      $table->string('image')->nullable();
      $table->unsignedBigInteger('author_id');
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('books');
  }
};
