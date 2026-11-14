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
        'branch_id',
        'payment_method',
        'cash_amount',
        'change_amount',
        'payment_note',
        'payment_proof',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'total_items' => 'integer',
            'total_amount' => 'integer',
            'cash_amount' => 'integer',
            'change_amount' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'qris' => 'QRIS',
            'transfer' => 'Transfer Bank',
            default => 'Cash (Tunai)',
        };
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        return $this->payment_proof ? asset('storage/' . $this->payment_proof) : null;
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
