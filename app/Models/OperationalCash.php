<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalCash extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time',
        'branch_id',
        'user_id',
        'type',
        'category',
        'name',
        'amount',
        'notes',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'integer',
    ];

    /**
     * Relasi ke Cabang
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relasi ke User pembuat
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label jenis transaksi kas
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'tabungan' ? 'Tabungan / Setoran' : 'Belanja Operasional';
    }

    /**
     * Scope untuk pengeluaran belanja
     */
    public function scopePengeluaran(Builder $query): Builder
    {
        return $query->where('type', 'pengeluaran');
    }

    /**
     * Scope untuk tabungan
     */
    public function scopeTabungan(Builder $query): Builder
    {
        return $query->where('type', 'tabungan');
    }
}
