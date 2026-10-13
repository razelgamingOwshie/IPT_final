<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;

class Sale extends Model
{
    use HasFactory;

    public const PAYMENT_METHODS = ['cash', 'card', 'mobile'];

    public $timestamps = false;

    protected $fillable = ['customer_name', 'total', 'payment_method', 'sale_date'];

    protected static function booted(): void
    {
        static::creating(function (Sale $sale): void {
            $sale->sale_date ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'sale_date' => 'datetime',
        ];
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('sale_date', today());
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function recomputeTotal(): void
    {
        $totalCents = (int) round((float) $this->items()->sum('line_total') * 100);

        if ($totalCents > 99_999_999) {
            throw ValidationException::withMessages([
                'total' => 'A sale total cannot exceed $999,999.99.',
            ]);
        }

        $this->update([
            'total' => number_format($totalCents / 100, 2, '.', ''),
        ]);
    }
}
