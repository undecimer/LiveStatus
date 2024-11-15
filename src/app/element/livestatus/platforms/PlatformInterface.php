<?php

namespace YOOtheme\LiveStatus\Element\LiveStatus\Platforms;

interface PlatformInterface
{
    /**
     * Check if a channel is currently live
     *
     * @return bool True if live, false otherwise
     */
    public function isLive(): bool;

    /**
     * Fetch data from the platform
     *
     * @return array The platform data
     * @throws \Exception If the request fails
     */
    public function fetchData(): array;
}
