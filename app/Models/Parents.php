<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class Parents extends Model
{
    use HasApiTokens;

    protected $table = 'parents';

    protected $fillable = [
        'password',
        'phone',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
