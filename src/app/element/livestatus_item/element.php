<?php

namespace YOOtheme\LiveStatus\Element;

use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\TikTok;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\YouTube;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Twitch;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\FacebookLive;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\InstagramLive;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Kick;

return [
    'transforms' => [
        'render' => function ($node) {
            // Initialize platform data
            $platform = strtolower($node->props['platform'] ?? 'tiktok');
            $username = $node->props['username'] ?? '';

            if (empty($username)) {
                $node->props['platformData'] = ['error' => 'Username is required', 'live' => false];
                return $node;
            }

            try {
                // Create platform instance based on selection
                $instance = null;
                switch ($platform) {
                    case 'tiktok':
                        $instance = new TikTok($username);
                        break;
                    case 'youtube':
                        $instance = new YouTube($username);
                        break;
                    case 'twitch':
                        $instance = new Twitch($username);
                        break;
                    case 'facebook':
                        $instance = new FacebookLive($username);
                        break;
                    case 'instagram':
                        $instance = new InstagramLive($username);
                        break;
                    case 'kick':
                        $instance = new Kick($username);
                        break;
                }

                if (!$instance) {
                    throw new \Exception("Invalid platform: {$platform}");
                }

                // Get live status
                $isLive = $instance->isLive();
                error_log("LiveStatus item {$platform}@{$username} status: " . ($isLive ? 'live' : 'not live'));
                
                // Store platform data in node props
                $node->props['platformData'] = [
                    'live' => $isLive,
                    'error' => null
                ];
            } catch (\Exception $e) {
                // Log error and store error message
                error_log("Error fetching platform data: " . $e->getMessage());
                $node->props['platformData'] = [
                    'error' => $e->getMessage(),
                    'live' => false
                ];
            }

            return $node;
        }
    ]
];
