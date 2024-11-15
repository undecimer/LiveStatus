<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus;

return [
    'transforms' => [
        'render' => function ($node) {
            error_log("LiveStatus render start - Children count: " . count($node->children ?? []));
            
            // Ensure we have children to process
            if (empty($node->children)) {
                error_log("LiveStatus: No children to process");
                return $node;
            }

            // Process each child node
            foreach ($node->children as $child) {
                error_log("LiveStatus: Processing child node - Type: " . ($child->type ?? 'unknown'));
                if (!isset($child->props)) {
                    $child->props = [];
                }
            }

            error_log("LiveStatus render complete");
            return $node;
        },
        
        'transform' => function ($node, $params) {
            error_log("LiveStatus transform start");
            
            // Get platform data for each child
            foreach ($node->children as $child) {
                if ($child->type === 'livestatus_item') {
                    error_log("LiveStatus transform: Processing livestatus_item");
                    $child->props['parent'] = $node->props;
                }
            }
            
            error_log("LiveStatus transform complete");
            return $node;
        }
    ]
];
