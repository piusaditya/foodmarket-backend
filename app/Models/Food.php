<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Food extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'ingredients', 'price', 'rate', 'types',
        'picturePath'
    ];

    // Accessors change format date to UNIX timestamps (epoch)
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function geUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    //add function for accessors and mutators to read camel case table field picturePath
    //function for laravel to read the picturePath not picture_path
    public function toArray()
    {
        $toArray = parent::toArray();
        $toArray['picturePath'] = $this->picturePath;
        return $toArray;
    }

    // Accessor will read as picturePath not picture_path
    // Accessor return to full url of picturePath
    public function getPicturePathAttribute()
    {
        return url('') . Storage::url($this->attributes['picturePath']);
    }
}
