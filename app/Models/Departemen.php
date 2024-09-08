<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'user_id'
    ];

    public function classes()
    {
        return $this->hasMany(Classes::class);
    }
    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasOne(Student::class);
    }
}
