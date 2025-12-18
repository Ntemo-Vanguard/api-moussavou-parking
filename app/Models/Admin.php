<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Utilisateur;

class Admin extends Utilisateur
{
    protected $attributes = ['role' => 'admin'];
    protected static function booted()
    {
        static::creating(fn($model) => $model->role = 'admin');

        static::addGlobalScope('admin_role', function (Builder $builder) {
            $builder->where('role', 'admin');
        });
    }
}