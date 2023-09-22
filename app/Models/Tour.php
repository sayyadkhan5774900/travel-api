<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tour extends Model
{
    use HasFactory, HasUuids;


    protected $fillable = [
        'name',
        'starting_date',
        'ending_date',
        'price',
    ];

    protected $casts = [
        'starting_date' => 'date',
        'ending_date' => 'date',
    ];

    /**
     * Get the travel that owns the Tour
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }


    public function price() : Attribute {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100
        );
    }


}
