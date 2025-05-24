<?php

declare(strict_types=1);

namespace Patoui\ModelHistory\Contracts;

interface TrackModelHistoryContract
{
    public function getNewModelHistoryProperties(): array;

    public function getOldModelHistoryProperties(): array;
}
