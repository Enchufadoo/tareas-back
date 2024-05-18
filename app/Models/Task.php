<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const TASK_FINISHED = 1;
    const TASK_UNFINISHED = 0;

    protected $fillable = [
        'title',
        'user_id,',
        'finished',
        'description',
        'end_date'
    ];

    protected $table = 'tasks';

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class)->select(['id', 'username', 'email', 'avatar']);
    }

    public function taskProgress()
    {
        return $this->hasMany(TaskProgress::class);
    }

    public function scopeCurrentUser(Builder $query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopeFinished(Builder $query)
    {
        return $query->where('finished', self::TASK_FINISHED);
    }

    public function scopeUnfinished(Builder $query)
    {
        return $query->where('finished', self::TASK_UNFINISHED);
    }
}
