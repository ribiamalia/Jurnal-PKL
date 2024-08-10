<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'name',
    'no_hp',
   'departemen_id',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function departements()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id');
    }

    public function students()
    {
        return $this->hasOne(Student::class);
    }
}
