<?php

namespace YOOtheme\LiveStatus;

defined('_JEXEC') or die;

use YOOtheme\Builder;
use YOOtheme\Path;

// Register autoloader for all LiveStatus classes
spl_autoload_register(function ($class) {
    // Base namespace for our plugin
    $base_namespace = 'YOOtheme\\LiveStatus\\';
    
    // Check if the class belongs to our namespace
    if (strpos($class, $base_namespace) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, strlen($base_namespace));

    // Special handling for platform classes
    if (strpos($relative_class, 'Element\\LiveStatus\\Platforms\\') === 0) {
        $platform_class = substr($relative_class, strlen('Element\\LiveStatus\\Platforms\\'));
        $file = __DIR__ . '/app/element/livestatus/platforms/' . str_replace('\\', '/', $platform_class) . '.php';
    } else {
        // For all other classes
        $file = __DIR__ . '/app/' . str_replace('\\', '/', $relative_class) . '.php';
    }

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