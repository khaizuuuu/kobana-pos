<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'file_name',
        'status',
        'rows_menu',
        'rows_transactions',
        'rows_items',
        'warnings',
        'created_by',
        'created_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'rows_menu' => 'integer',
            'rows_transactions' => 'integer',
            'rows_items' => 'integer',
            'warnings' => 'array',
            'created_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
