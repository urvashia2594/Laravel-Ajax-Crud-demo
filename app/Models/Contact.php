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

    protected static function boot() {
        parent::boot();
    
        static::deleted(function ($contact) {
          $contact->merged()->delete();
        });
    }

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
        return $this->hasMany(MergedContact::class, 'master_id','id');
    }

    // protected $casts = ['custom_fields' => 'array'];
}
