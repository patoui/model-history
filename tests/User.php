<?php

declare(strict_types=1);

namespace Patoui\ModelHistory\Tests;

use Illuminate\Database\Eloquent\Model;
use Patoui\ModelHistory\Contracts\TrackModelHistoryContract;
use Patoui\ModelHistory\Traits\TrackModelHistory;

class User extends Model implements TrackModelHistoryContract
{
    use TrackModelHistory;

    protected $fillable = [
        'name',
        'email',
    ];
}
