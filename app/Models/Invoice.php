<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    public function feeDetail()
    {
        return $this->belongsTo(FeeDetail::class, 'fee_detail_id');
    }
}
