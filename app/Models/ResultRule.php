<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultRule extends Model
{
    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(ResultRuleDetail::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
