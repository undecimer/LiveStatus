<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus;

use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\TikTok;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\YouTube;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Twitch;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\FacebookLive;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\InstagramLive;
use YOOtheme\LiveStatus\Element\LiveStatus\Platforms\Kick;

return [
    'transforms' => [
        'render' => function ($node) {
            // Initialize platform instance based on selection
            $platform = strtolower($node->props['platform'] ?? 'tiktok');
            $username = $node->props['username'] ?? '';

            error_log("LiveStatus render start - Platform: {$platform}, Username: {$username}");

            if (empty($username)) {
                error_log("LiveStatus: Empty username");
                return false;
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
                    error_log("LiveStatus: Invalid platform {$platform}");
                    return false;
                }

                // Get live status
                $data = $instance->fetchData();
                error_log("LiveStatus fetchData result: " . print_r($data, true));
                
                // Update node props with live status and data
                $node->props['isLive'] = $data['live'] ?? false;
                $node->props['platformData'] = $data;
                
                error_log("LiveStatus node props updated - isLive: " . ($node->props['isLive'] ? 'true' : 'false'));
                return $node;
            } catch (\Exception $e) {
                // Log error and store it in node props
                error_log("LiveStatus Error: " . $e->getMessage());
                $node->props['platformData'] = ['error' => $e->getMessage()];
                return $node;
            }
        }
    ]
];
