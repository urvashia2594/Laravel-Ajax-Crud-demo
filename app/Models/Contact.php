<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MergedContact;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'prof_img',
        'doc',
        'custom_fields'
    ];

    public function merged()
    {
        return $this->hasMany(MergedContact::class, 'master_contact_id');
    }

    // protected $casts = ['custom_fields' => 'array'];
}
