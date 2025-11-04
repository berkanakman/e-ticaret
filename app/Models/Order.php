<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders'; // tablo adı çoğul
    protected $fillable = [
        'user_id',
        'admin_id',
        'status',
        'total',
    ];
}
