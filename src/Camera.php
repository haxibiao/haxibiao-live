<?php

namespace Haxibiao\Live;

use App\Store;
use App\User;
use Haxibiao\Live\Traits\CameraAttrs;
use Haxibiao\Live\Traits\CameraRepo;
use Haxibiao\Live\Traits\CameraResolvers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Camera extends Model
{
    use CameraRepo;
    use CameraResolvers;
    use CameraAttrs;


    protected $guarded = [];

    //实况可见性
    const all = '["*"]';
    const self = '[]';
    const auth = 'auth';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public const protocol = 'rtmp';

    public const prefix = self::protocol . '://';

    const STATUS_OFFLINE = -1;
    const STATUS_ONLINE  = 0;

    public static function getVisibility()
    {
        return [
            self::all => 'all',
            self::self => 'self'
        ];
    }
}
