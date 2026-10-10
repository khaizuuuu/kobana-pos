<?php
// app/Models/FpAnalysis.php
namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\HasMany};

class FpAnalysis extends Model
{
    protected $fillable = [
        'name',
        'date_from',
        'date_to',
        'min_support',
        'min_confidence',
        'total_transactions',
        'status',
        'error_message',
        'started_at',
        'finished_at',
        'created_by',
    ];

    protected $casts = [
        'date_from'      => 'date',
        'date_to'        => 'date',
        'min_support'    => 'float',
        'min_confidence' => 'float',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
    ];

    /** @return BelongsTo */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany */
    public function itemsets(): HasMany
    {
        return $this->hasMany(FpFrequentItemset::class, 'analysis_id');
    }

    /** @return HasMany */
    public function rules(): HasMany
    {
        return $this->hasMany(FpAssociationRule::class, 'analysis_id');
    }
}
