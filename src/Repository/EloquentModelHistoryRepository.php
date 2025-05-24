<?php

declare(strict_types=1);

namespace Patoui\ModelHistory\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Patoui\ModelHistory\Contracts\ModelHistoryRepositoryContract;
use Patoui\ModelHistory\Contracts\TrackModelHistoryContract;
use Patoui\ModelHistory\Models\ModelHistory;

class EloquentModelHistoryRepository implements ModelHistoryRepositoryContract
{
    public function store(TrackModelHistoryContract&Model $model): void
    {
        $old = $model->getOldModelHistoryProperties();
        $new = $model->getNewModelHistoryProperties();

        if (empty($old) && empty($new)) {
            return;
        }

        /** @var class-string<ModelHistory> $modelClass */
        $modelClass = config('model-history.model');

        /** @var ModelHistory $change */
        $change = new $modelClass;
        $change->model_type = $model->getMorphClass();
        $change->model_id = $model->getKey();
        $change->old = $old;
        $change->new = $new;
        $change->auth_id = Auth::user()?->getAuthIdentifier();
        $change->created_at = now()->toImmutable()->format('Y-m-d H:i:s.v');
        $change->save();
    }
}
