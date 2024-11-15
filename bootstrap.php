<?php

namespace YOOtheme\LiveStatus;

use YOOtheme\Builder;
use YOOtheme\Path;

return [
    'extend' => [
        Builder::class => static function (Builder $builder) {
            $builder->addTypePath(Path::get('./src/app/element/*/element.json'));
        },
    ],
];