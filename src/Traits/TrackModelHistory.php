<?php

declare(strict_types=1);

namespace Patoui\ModelHistory\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Patoui\ModelHistory\Contracts\ModelHistoryRepositoryContract;
use Patoui\ModelHistory\Contracts\TrackModelHistoryContract;

trait TrackModelHistory
{
    protected static function bootTrackModelHistory(): void
    {
        static::saved(function (TrackModelHistoryContract&Model $model) {
            /** @var ModelHistoryRepositoryContract $changeRepository */
            $changeRepository = App::make(ModelHistoryRepositoryContract::class);
            $changeRepository->store($model);
        });
    }

    public function getNewModelHistoryProperties(): array
    {
        return Arr::except($this->getDirty(), $this->getModelHistoryExcludedProperties());
    }

    public function getOldModelHistoryProperties(): array
    {
        $original = $this->getRawOriginal();
        $new = $this->getNewModelHistoryProperties();

        $old = array_intersect_key($original, $new);

        return Arr::except($old, $this->getModelHistoryExcludedProperties());
    }

    public function getModelHistoryExcludedProperties(): array
    {
        return $this->modelHistoryExcludedProperties ?? config('model-history.exclude_properties', []);
    }
}
