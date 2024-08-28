<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'industri_id', 'date', 'status'
    ];

    public function users() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function industries() {
        return $this->belongsTo(Industry::class, 'industri_id');
    }
}
