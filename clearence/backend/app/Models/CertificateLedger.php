<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateLedger extends Model
{
    public $timestamps = false;

    protected $table = 'certificate_ledger';

    protected $fillable = [
        'clearance_id',
        'certificate_hash',
        'previous_hash',
        'sequence',
        'signed_at',
        'signature',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
            'sequence'  => 'integer',
        ];
    }

    public function clearance()
    {
        return $this->belongsTo(Clearance::class);
    }
}
