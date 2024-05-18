<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingValue extends Model
{
    use HasFactory;

    protected $table = 'settings_values';
    protected $fillable = [
        'setting_id',
        'value'
    ];

    public function setting()
    {
        return $this->belongsTo(SettingValue::class, 'setting_id', 'id');
    }
}
