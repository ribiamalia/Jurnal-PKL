<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AspekProduktif extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'industri_id', 'name', 'score'
    ];

    public function students(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function industries(){
        return $this->belongsTo(Industry::class, 'industri_id');
    }



}
