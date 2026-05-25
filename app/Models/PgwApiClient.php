<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class PgwApiClient extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'api_clients';

    protected $guarded = ['id'];
}
