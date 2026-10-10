<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'transaction_date',
        'transaction_time',
        'total_items',
        'total_amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'total_items' => 'integer',
            'total_amount' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getHariIndonesiaAttribute(): string
    {
        return nama_hari($this->transaction_date);
    }

    public function getFormattedAmountAttribute(): string
    {
        return format_rupiah($this->total_amount);
    }

    public function getFormattedDateAttribute(): string
    {
        return format_tanggal($this->transaction_date);
    }
}
