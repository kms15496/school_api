<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'school_class_id',
        'result_rule_id',
        'code',
        'optional'
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function resultRule()
    {
        return $this->belongsTo(ResultRule::class);
    }

    public function resultRuleDetails()
    {
        return $this->hasManyThrough(
            ResultRuleDetail::class,  // final model
            ResultRule::class,        // intermediate
            'id',                     // FK on ResultRule table (local key for details)
            'result_rule_id',         // FK on ResultRuleDetail table
            'result_rule_id',         // FK on Subject table
            'id'                      // local key on ResultRule table
        );
    }
}
