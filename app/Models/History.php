<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class History extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    // Определите поля, которые можно массово присвоить
    protected $fillable = [
        'id',
        'model_id',
        'model_name',
        'before',
        'after',
        'action',
    ];

    // Определите кастинг для полей before и after
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    // Обеспечьте создание UUID при создании записи
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}
