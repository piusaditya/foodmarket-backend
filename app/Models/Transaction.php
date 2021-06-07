<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'food_id', 'user_id', 'quantity', 'total', 'status', 'payment_url'
    ];

    //relationship transaaction to food entity
    //foreign key id of food and local key food_id
    public function food()
    {
        return $this->hasOne(Food::class, 'id', 'food_id');
    }

    //relationship transaaction to user entity
    //foreign key id of user and local key user_id
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // Accessor change format date to UNIX timestamps (epoch)
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function geUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
}
