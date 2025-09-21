<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Userlogin extends Authenticatable {
    protected $table = 'userlogin';
    protected $primaryKey = 'userID';
    public $timestamps = true;

    protected $fillable = [
        'username',
        'password',
        'fullName',
        'role',
    ];

    protected $hidden = [
        'password',
    ];
    
    const ROLE_OPTIONS = ['Owner', 'Purchase', 'Sales'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = strlen($value) < 60
            ? Hash::make($value) : $value;
    }
}
