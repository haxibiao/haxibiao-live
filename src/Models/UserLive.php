<?php

namespace Haxibiao\Live\Models;

use App\User;
use App\Video;
use Haxibiao\Live\Traits\UserLiveRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLive extends Model
{
    protected $table = 'user_lives';

    protected $casts = [
        'data' => 'array',
    ];

    use UserLiveRepo;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function live(): BelongsTo
    {
        return $this->belongsTo(LiveRoom::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
