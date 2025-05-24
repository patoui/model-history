<?php

declare(strict_types=1);

return [
    /**
     * The model to use for storing the changes.
     */
    'model' => \Patoui\ModelHistory\Models\ModelHistory::class,

    /**
     * The properties that will be excluded from the model history.
     * If these are the only modified properties, the model history will not be created.
     */
    'exclude_properties' => [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ],
];
