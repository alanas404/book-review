<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book;

class Review extends Model
{
    use HasFactory;
    protected $fillable = ['review','rating'];
    public function book(){
        return $this->belongsTo(Book::class);
    }

    protected static function booted(): void
    {
        static::updated(function (Review $review) {
            cache()->forget('book:'.$review->book->id);
        });

        static::deleted(function (Review $review) {
            cache()->forget('book:'.$review->book->id);
        });
        static::created(function (Review $review) {
            cache()->forget('book:'.$review->book->id);
        });
    }
}
