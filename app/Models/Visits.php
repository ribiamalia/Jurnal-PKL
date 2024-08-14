<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Visits extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'industri_id', 'visitDate', 'visitReport', 'image'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function industri()
    {
        return $this->belongsTo(Industry::class, 'industri_id');
    }

    
    protected function image(): Attribute
    {
       return Attribute::make(
           get: fn ($image) => asset('/storage/' . $image),
        );
       }
}
