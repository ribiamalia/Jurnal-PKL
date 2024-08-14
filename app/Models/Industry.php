<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
       'name',
       'bidang',
       'alamat',
       'longitude',
       'latitude',
        'industryMentorName',
        'industryMentorNo',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students()
    {
        return $this->hasOne(Student::class);
    }

    public function visits()
    {
        return $this->hasMany(Visits::class);
    }
}
