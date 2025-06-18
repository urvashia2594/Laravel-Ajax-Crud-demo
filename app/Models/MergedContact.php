<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergedContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'merged_ids',
        'master_id',
        'name',
        'email',
        'phone',
        'gender',
        'image',
        'document',
        'custom_fields',
    ];

    protected $casts = [
        'merged_ids' => 'array',
        'custom_fields' => 'array',
    ];

    public function master()
    {
        return $this->belongsTo(Contact::class, 'master_id');
    }

}
