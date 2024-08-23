<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; 
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'password',
       
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [

        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function industries()
    {
        return $this->hasOne(Industry::class);
    }

    public function teachers()
    {
        return $this->hasOne(Teacher::class);
    }

    public function parents()
    {
        return $this->hasOne(Parents::class);
    }

    public function students()
    {
        return $this->hasOne(Student::class);
    }

    
    public function guides()
    {
        return $this->hasOne(Guide::class);
    }

    public function visits()
    {
        return $this->hasMany(Visits::class);
    }
   
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
   

    
}
