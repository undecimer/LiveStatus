<?php

namespace YOOtheme\LiveStatus\Cache;

use Joomla\CMS\Factory;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Plugin\PluginHelper;

class CacheManager
{
    private static $instance = null;
    private $cache;
    private $group = 'plg_system_livestatus';
    private $cacheTime;

    private function __construct()
    {
        try {
            $plugin = PluginHelper::getPlugin('system', 'livestatus');
            $params = json_decode($plugin->params);
            $this->cacheTime = isset($params->cache_time) ? (int)$params->cache_time : 30;

            // Initialize cache with file handler
            $options = array(
                'defaultgroup' => $this->group,
                'storage' => 'file',
                'caching' => true,
                'lifetime' => $this->cacheTime * 60 // Convert to minutes
            );

            $this->cache = Factory::getCache($this->group, 'file');
            $this->cache->setCaching(true);
            $this->cache->setLifeTime($this->cacheTime * 60);

            error_log("Cache initialized with file handler for group {$this->group}");
        } catch (\Exception $e) {
            error_log("Cache initialization error: " . $e->getMessage());
            throw $e;
        }
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
        try {
            $key = $this->getCacheKey($platform, $username);
            
            // Get from cache
            $data = $this->cache->get($key);
            
            if ($data === false) {
                error_log("Cache miss for {$platform}_{$username}");
                return null;
            }
            
            error_log("Cache hit for {$platform}_{$username}: " . ($data['live'] ? 'live' : 'not live'));
            return $data['live'];
            
        } catch (\Exception $e) {
            error_log("Cache get error for {$platform}_{$username}: " . $e->getMessage());
            return null;
        }
    }

    public function store($data, string $platform, string $username, int $lifetime = null): bool
    {
        try {
            $key = $this->getCacheKey($platform, $username);
            
            // Prepare data for storage
            $cacheData = array(
                'live' => ($data === true || $data === 1 || $data === '1'),
                'platform' => $platform,
                'username' => $username,
                'timestamp' => time()
            );
            
            // Set lifetime if provided
            if ($lifetime !== null) {
                $this->cache->setLifeTime($lifetime);
            }
            
            // Store data
            $result = $this->cache->store($key, $cacheData);
            
            error_log("Cache store for {$platform}_{$username}: " . ($cacheData['live'] ? 'live' : 'not live'));
            
            return $result;
        } catch (\Exception $e) {
            error_log("Cache store error for {$platform}_{$username}: " . $e->getMessage());
            return false;
        }
    }

    public function clear(string $platform, string $username): bool
    {
        try {
            $key = $this->getCacheKey($platform, $username);
            $result = $this->cache->remove($key);
            error_log("Cache cleared for {$platform}_{$username}");
            return $result;
        } catch (\Exception $e) {
            error_log("Cache clear error for {$platform}_{$username}: " . $e->getMessage());
            return false;
        }
    }

    public function clean(): bool
    {
        try {
            $result = $this->cache->clean($this->group);
            error_log("Cache cleaned for group {$this->group}");
            return $result;
        } catch (\Exception $e) {
            error_log("Cache clean error: " . $e->getMessage());
            return false;
        }
    }

    private function getCacheKey(string $platform, string $username): string
    {
        // Create a simple but unique key
        return md5('livestatus.' . strtolower($platform) . '.' . strtolower($username));
    }
}
