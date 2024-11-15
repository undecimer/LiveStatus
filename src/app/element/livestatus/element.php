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

            if (empty($username)) {
                return false;
            }

            try {
                // Create platform instance
                switch ($platform) {
                    case 'tiktok':
                        $instance = new TikTok();
                        break;
                    case 'youtube':
                        $instance = new YouTube();
                        break;
                    case 'twitch':
                        $instance = new Twitch();
                        break;
                    case 'facebook':
                        $instance = new FacebookLive();
                        break;
                    case 'instagram':
                        $instance = new InstagramLive();
                        break;
                    case 'kick':
                        $instance = new Kick();
                        break;
                    default:
                        return false;
                }

                // Get live status
                $isLive = $instance->isLive($username);

                // Update node props with live status
                $node->props['is_live'] = $isLive;
                $node->props['platform_name'] = $platform;
                $node->props['channel_username'] = $username;

                return $node;
            } catch (\Exception $e) {
                // Handle any errors gracefully
                $node->props['is_live'] = false;
                $node->props['platform_name'] = $platform;
                $node->props['channel_username'] = $username;
                $node->props['error'] = $e->getMessage();
                return $node;
            }
        }
    ]
];
