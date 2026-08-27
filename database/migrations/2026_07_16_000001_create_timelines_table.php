<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The name of the table.
     *
     * @var string
     */
    public const TABLE_NAME = 'timelines';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('locale')->index();
            $table->string('slug');
            $table->string('title');
            $table->text('tldr')->nullable();
            $table->json('part_one');
            $table->json('part_two');
            $table->string('translation_key')->index();
            $table->timestamps();
            $table->unique(['locale', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
};
