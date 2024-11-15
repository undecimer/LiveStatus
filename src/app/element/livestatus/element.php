<?php

namespace YOOtheme\Builder;

return [
    'transforms' => [
        'render' => function ($node) {
            // Initialize Live Status element
            $node->props['_livestatus'] = true;
        },
    ],
];
