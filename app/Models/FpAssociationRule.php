<?php
// app/Models/FpAssociationRule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FpAssociationRule extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'analysis_id',
        'antecedent',
        'consequent',
        'antecedent_label',
        'consequent_label',
        'support',
        'confidence',
        'lift',
    ];

    protected $casts = [
        'antecedent' => 'array',
        'consequent' => 'array',
        'support'    => 'float',
        'confidence' => 'float',
        'lift'       => 'float',
    ];

    /** @return BelongsTo */
    public function analysis(): BelongsTo
    {
        return $this->belongsTo(FpAnalysis::class, 'analysis_id');
    }
}
