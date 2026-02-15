<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultRuleDetail extends Model
{
    protected $guarded = ['id'];

    public function resultRule()
    {
        return $this->belongsTo(ResultRule::class);
    }
}
