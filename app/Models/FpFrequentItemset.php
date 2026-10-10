<?php
// app/Models/FpFrequentItemset.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FpFrequentItemset extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'analysis_id',
        'items',
        'items_label',
        'itemset_size',
        'support_count',
        'support',
    ];

    protected $casts = [
        'items'          => 'array',
        'itemset_size'   => 'integer',
        'support_count'  => 'integer',
        'support'        => 'float',
    ];

    /** @return BelongsTo */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(FpAnalysis::class, 'analysis_id');
    }
}
