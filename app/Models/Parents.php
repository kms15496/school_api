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
        'name',
        'active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
