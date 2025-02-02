<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Заполнение таблицы авторами (поскольку реализуется API только для работы с книгами я решил заполнять авторов автоматически)
        DB::table('authors')->insert([
            ['name' => 'Лев Толстой', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Федор Достоевский', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Александр Пушкин', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Антон Чехов', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Николай Гоголь', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
