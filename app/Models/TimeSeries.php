<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSeries extends Model
{
    use HasFactory;

    protected $fillable = ['symbol', 'date', 'open', 'high', 'low', 'close', 'adjusted_close', 'volume'];

    protected $casts = [
        'adjusted_close'  => 'float',
    ];
}
