<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
            'user_id',
            'date',
            'departureTime',
            'arrivalTime',
            'absenceReason',
            'image',
            'longitude',
            'latitude',
            'status'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // app/Models/Parents.php

public function attendances()
{
    return $this->hasManyThrough(Attendance::class, Student::class, 'parent_id', 'user_id', 'id', 'user_id');
}


    protected function image(): Attribute
    {
       return Attribute::make(
           get: fn ($image) => asset('/storage/' . $image),
        );
       }
}
