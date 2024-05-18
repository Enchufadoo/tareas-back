<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'type',
        'default'
    ];

    public function defaultValue()
    {
        return $this->belongsTo(SettingValue::class, 'default', 'id');
    }

    public function fromUser()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function values()
    {
        return $this->hasOne(UserSetting::class);
    }
}
