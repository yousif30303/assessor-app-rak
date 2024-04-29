<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Assessor extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;


    protected $hidden = [
        'password',
        'id',
        'deleted_at'
    ];

    protected $fillable = [
        'password',
        'first_login'
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', 1);
        });
    }

    protected $casts = [
        'status' => 'boolean',
        'first_login' => 'boolean',
        'created_at' => 'datetime:d-m-Y H:i:s',
        'updated_at' => 'datetime:d-m-Y H:i:s',
    ];

    public function details()
    {
        return (new BdcmsAssessor())->getAccessor($this->sb_id);
    }

    public function setPasswordAttribute($value)
    {
        return $this->attributes['password'] = Hash::make($value);
    }
}
