<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Models\Review;

class Book extends Model
{
    use HasFactory;
    public function reviews(){
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $query,string $title):Builder{
        return $query->where('title','LIKE','%'.$title.'%');
    }

    public function scopeWithReviewsCount(Builder $query,$from=null,$to=null):Builder | QueryBuilder{
        return $query->withCount(['reviews'=> fn(Builder $q) => $this->dateRangeFilter($q,$from,$to)]);
    }

    public function scopeWithAvgRating(Builder $query,$from=null,$to=null){
        return $query->withAvg(['reviews'=> fn(Builder $q) => $this->dateRangeFilter($q,$from,$to)],'rating');
    }

    public function scopePopular(Builder $query,$from=null,$to=null):Builder | QueryBuilder {
      return $query->withReviewsCount($from,$to)->orderBy('reviews_count','desc');
    }


    public function scopeHighestRated(Builder $query,$from=null,$to=null):Builder|QueryBuilder{
        return $query->withAvgRating($query,$from,$to)->orderBy('reviews_avg_rating','desc');
    }

    public function scopeMinReviews(Builder $query,int $minReviews):Builder | QueryBuilder{
        return $query->having('reviews_count','>=',$minReviews);
    }

    public function scopePopularLastMonth(Builder $query):Builder | QueryBuilder{
        return $query->popular(now()->subMonth(),now())
        ->highestRated(now()->subMonth(),now())
        ->minReviews(2);
      }

      public function scopePopularLast6Months(Builder $query):Builder | QueryBuilder{
          return $query->popular(now()->subMonth(6),now())
          ->highestRated(now()->subMonth(6),now())
          ->minReviews(5);
      }

      public function scopeHighestRatedLastMonth(Builder $query):Builder | QueryBuilder{
          return $query->highestRated(now()->subMonth(),now())
          ->popular(now()->subMonth(),now());

      }

      public function scopeHighestRatedLast6Months(Builder $query):Builder | QueryBuilder{
          return $query->highestRated(now()->subMonth(6),now())
          ->popular(now()->subMonth(6),now())->minReviews(5);

      }

    private function dateRangeFilter(Builder $query,$from=null,$to=null){
        if($from && !$to){
            $query->where('created_at','>=',$from);
         }
         else if(!$from && $to){
            $query->where('created_at','<=',$to);
         }
         else if($from && $to){
            $query->whereBetween('created_at',[$from,$to]);
         }
    }


    protected static function booted(): void
    {
        static::updated(function (Book $book) {
            cache()->forget('book:'.$book->id);
        });

        static::deleted(function (Review $review) {
            cache()->forget('book:'.$book->id);
        });
    }


}
