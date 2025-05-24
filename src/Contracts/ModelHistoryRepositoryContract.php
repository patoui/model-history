<?php

declare(strict_types=1);

namespace Patoui\ModelHistory\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ModelHistoryRepositoryContract
{
    /**
     * @param  TrackModelHistoryContract&Model  $model  The model that was changed
     */
    public function store(TrackModelHistoryContract&Model $model): void;
}
