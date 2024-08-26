<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Student extends Model
{
    use HasFactory;

           protected $fillable = [
            'user_id',
            'name',
            'nis',
            'placeOfBirth',
            'dateOfBirth',
            'gender',
            'bloodType',
            'alamat',
            'image',
            'classes_id',
            'industri_id',
            'departemen_id',
            'parents_id',
            'teacher_id',
           ];

           public function classes (){
            return $this->belongsTo(Classes::class, 'classes_id');
        }
        public function parents()
        {
            return $this->belongsTo(Parents::class, 'parents_id');
        }
           public function departements (){
            return $this->belongsTo(Departemen::class, 'departemen_id');
        }
           public function teachers(){
            return $this->belongsTo(Teacher::class, 'teacher_id');
        }
           public function industries (){
            return $this->belongsTo(Industry::class, 'industri_id');
        }
           public function users (){
            return $this->belongsTo(User::class, 'user_id');
        }

        protected function image(): Attribute
     {
        return Attribute::make(
            get: fn ($image) => asset('/storage/' . $image),
         );
        }
}
