<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

interface PlatformInterface
{
    /**
     * Check if a channel is currently live
     *
     * @param string $username The channel username
     * @return bool True if live, false otherwise
     */
    public function isLive(string $username): bool;
}
