<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'judul',
     'date',
     'start_time',
        'end_time',
        'description',
           'tools',
           'image'

    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected function image(): Attribute
    {
       return Attribute::make(
           get: fn ($image) => asset('/storage/' . $image),
        );
       }
}
