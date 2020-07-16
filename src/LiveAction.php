<?php

namespace Haxibiao\Live;

use App\User;
use Haxibiao\Live\Traits\LiveActionRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveAction extends Model
{

    use LiveActionRepo;

    protected $guarded = [];

    public function live(): BelongsTo
    {
        return $this->belongsTo(Live::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
