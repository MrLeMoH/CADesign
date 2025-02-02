<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    // Указываем таблицу, с которой работает модель (если она не совпадает с именем модели)
    protected $table = 'reservations';

    // Определяем, какие поля можно массово заполнять
    protected $fillable = [
        'user_id',
        'book_id',
        'reserved_at',
        'returned_at',
    ];

    // Связь с моделью User (пользователь, который забронировал книгу)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Связь с моделью Book (книга, которая была забронирована)
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
