<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RedisLock
{
    private const DEFAULT_TTL = 10; // 10 seconds default TTL
    private const RETRY_DELAY = 100; // 100ms delay between retries
    private const MAX_RETRIES = 3;
    private const RELEASE_RETRY_DELAY = 50; // 50ms delay between release retries
    private const MAX_RELEASE_RETRIES = 5;

    /**
     * Attempt to acquire a lock
     *
     * @param string $key The lock key
     * @param int $ttl Time to live in seconds
     * @param int $retries Number of retries
     * @return string|false The lock token if successful, false otherwise
     */
    public function acquire(string $key, int $ttl = self::DEFAULT_TTL, int $retries = self::MAX_RETRIES): string|false
    {
        $token = Str::random(32);
        $attempts = 0;

        while ($attempts < $retries) {
            try {
                $locked = Redis::set(
                    $key,
                    $token,
                    'NX',
                    'EX',
                    $ttl
                );

                if ($locked) {
                    return $token;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to acquire lock for key: {$key}", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempts + 1
                ]);
            }

            $attempts++;
            if ($attempts < $retries) {
                usleep(self::RETRY_DELAY * 1000); // Convert to microseconds
            }
        }

        return false;
    }

    /**
     * Release a lock with retry mechanism
     *
     * @param string $key The lock key
     * @param string $token The lock token
     * @return bool Whether the lock was released successfully
     */
    public function release(string $key, string $token): bool
    {
        $attempts = 0;
        $script = <<<'LUA'
            if redis.call("get", KEYS[1]) == ARGV[1] then
                return redis.call("del", KEYS[1])
            else
                return 0
            end
        LUA;

        while ($attempts < self::MAX_RELEASE_RETRIES) {
            try {
                $result = Redis::eval($script, 1, $key, $token);
                if ($result === 1) {
                    return true;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to release lock for key: {$key}", [
                    'error' => $e->getMessage(),
                    'attempt' => $attempts + 1
                ]);
            }

            $attempts++;
            if ($attempts < self::MAX_RELEASE_RETRIES) {
                usleep(self::RELEASE_RETRY_DELAY * 1000); // Convert to microseconds
            }
        }

        // If we couldn't release the lock after all retries, log it but don't throw
        Log::error("Failed to release lock after {$attempts} attempts for key: {$key}");
        return false;
    }

    /**
     * Execute a callback while holding a lock
     *
     * @param string $key The lock key
     * @param callable $callback The callback to execute
     * @param int $ttl Time to live in seconds
     * @param int $retries Number of retries
     * @return mixed The result of the callback
     * @throws \Exception If lock cannot be acquired
     */
    public function executeWithLock(string $key, callable $callback, int $ttl = self::DEFAULT_TTL, int $retries = self::MAX_RETRIES)
    {
        $token = $this->acquire($key, $ttl, $retries);

        if (!$token) {
            throw new \Exception("Could not acquire lock for key: {$key}");
        }

        try {
            return $callback();
        } finally {
            // Always try to release the lock, even if the callback throws an exception
            $this->release($key, $token);
        }
    }
} 