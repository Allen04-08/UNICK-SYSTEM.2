<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Forecast extends Model
{
    protected $fillable = [
        'product_id','forecast_date','window_7','window_30','method'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}