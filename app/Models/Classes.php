<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'departemen_id'
    ];

    public function departemens()
    {
        return $this->belongsTo(Departemen::class, 'departemen_id');
    }
    public function students()
    {
        return $this->hasOne(Student::class);
    }
   
}
