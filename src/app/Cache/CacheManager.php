<?php

namespace YOOtheme\LiveStatus\Cache;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

class CacheManager
{
    private static $instance = null;
    private $cache;
    private $cacheTime;
    private $group = 'plg_system_livestatus';

    private function __construct()
    {
        $plugin = PluginHelper::getPlugin('system', 'livestatus');
        $params = json_decode($plugin->params);
        $this->cacheTime = isset($params->cache_time) ? (int)$params->cache_time : 30;

        $this->cache = Factory::getCache($this->group, 'output');
        $this->cache->setCaching(true);
        $this->cache->setLifeTime($this->cacheTime * 60); // Convert to minutes
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $platform, string $username)
    {
        $key = $this->getCacheKey($platform, $username);
        return $this->cache->get($key);
    }

    public function store($data, string $platform, string $username): bool
    {
        $key = $this->getCacheKey($platform, $username);
        return $this->cache->store($data, $key);
    }

    public function clean(): bool
    {
        return $this->cache->clean($this->group);
    }

    private function getCacheKey(string $platform, string $username): string
    {
        return md5($platform . '_' . $username);
    }
}
