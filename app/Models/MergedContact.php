<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MergedContact extends Model
{
    use HasFactory;

    protected $fillable = ['master_contact_id', 'merged_contact_id'];

    public function master()
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    public function merged()
    {
        return $this->belongsTo(Contact::class, 'merged_contact_id');
    }
}
