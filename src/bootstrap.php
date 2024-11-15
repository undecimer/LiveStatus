<?php

namespace YOOtheme\LiveStatus;

defined('_JEXEC') or die;

use YOOtheme\Builder;
use YOOtheme\Path;

// Register autoloader for platform classes
spl_autoload_register(function ($class) {
    // Check if the class belongs to our namespace
    $prefix = 'YOOtheme\\LiveStatus\\Element\\LiveStatus\\Platforms\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, strlen($prefix));

    // Convert class name to file path
    $file = __DIR__ . '/app/element/livestatus/platforms/' . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

return [
    'extend' => [
        Builder::class => static function (Builder $builder) {
            $builder->addTypePath(Path::get('./app/element/*/element.json'));
        },
    ],
];