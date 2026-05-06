<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeDetail extends Model
{
    protected $table = 'fee_details';

    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'fee_detail_id');
    }
}
