<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    use HasFactory;

    public const GRAPH_DATE_RANGE = [
        'from' => '2020-01-01',
        'to' => '2021-01-01',
    ];

    protected $fillable = ['symbol', 'fetch_date', 'name', 'industry', 'cap'];
}
