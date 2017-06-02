<?php

namespace Krak\Job\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use iter;

class PrefixCache implements CacheInterface
{
    private $cache;
    private $prefix;

    public function __construct(CacheInterface $cache, $prefix) {
        $this->cache = $cache;
        $this->prefix = $prefix;
    }

    public function get($key, $default = null) {
        return $this->cache->get($this->prefix.$key, $default);
    }
    public function set($key, $value, $ttl = null) {
        return $this->cache->set($this->prefix.$key, $value, $ttl);
    }
    public function delete($key) {
        return $this->cache->delete($this->prefix.$key);
    }
    public function clear() {
        return $this->clear();
    }
    public function getMultiple($keys, $default = null) {
        return $this->cache->getMultiple(iter\map($this->mapKey(), $keys), $default);
    }
    public function setMultiple($values, $ttl = null) {
        return $this->cache->setMultiple(iter\mapKeys($this->mapKey(), $values), $ttl);
    }
    public function deleteMultiple($keys) {
        return $this->cache->deleteMultiple(iter\map($this->mapKey(), $keys));
    }
    public function has($key) {
        return $this->cache->has($this->prefix.$key);
    }

    private function mapKey() {
        return function($key) {
            return $this->prefix.$key;
        };
    }

    public static function wrapInstanceName($instance_name, CacheInterface $cache = null) {
        if (!$cache) {
            return;
        }

        return new self($cache, 'krak_job_' . $instance_name . '_');
    }
}
