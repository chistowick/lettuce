<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\Interfaces\SaveableToCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * The class for receiving and processing currency rates from the Central Bank of the Russian Federation API.
 */
class ExchangeRatesHandler
{
    /** @var int $expiration The cache expiration time, in seconds.*/
    private $expiration = 60 * 60 * 24;

    /**
     * Interceptor method for creating short methods for fast currency conversion.
     *
     * @param string $method Method name
     * @param array $args Arguments (supposed only $args[0]  - date YYYY-MM-DD (by default = null))
     * @return string|null
     **/
    public function __call(string $method, array $args): ?string
    {
        preg_match('~^[A-Za-z]{3}To[A-Za-z]{3}$~', $method, $matches);

        if ($matches) {

            $currencies = explode('To', $matches[0]);

            $from = strtoupper($currencies[0]);
            $to = strtoupper($currencies[1]);

            $date = isset($args[0]) ? $args[0] : '';

            return $this->getFactor($from, $to, $date);
        }
    }

    /**
     * Returns the value of the coefficient for converting one currency to another on the selected date;
     *
     * @param string $from Source currency letter code
     * @param string $to Destination currency letter code
     * @param string $date Date (YYYY-MM-DD)
     * @return float|null Factor for converting $from to $to
     **/
    public function getFactor(string $from, string $to, string $date): ?float
    {
        return null;
    }

    /**
     * Saves a set of exchange rates to cache.
     *
     * @param SaveableToCache $group A set of instances ExchangeRates to save in cache.
     * @return void
     **/
    protected function saveToCache(SaveableToCache $group): void
    {
        try {
            $group->toCache($this->expiration);
        } catch (Exception $e) {
            Log::error("Exchange Rate: {$e->getMessage()}");
        }
    }

    /**
     * Search for data in the cache.
     *
     * @param string $from Source currency letter code
     * @param string $to Destination currency letter code
     * @param string $date Date (YYYY-MM-DD)
     * @return string|null
     **/
    protected function checkCache(string $from, string $to, string $date): ?string
    {
        try {
            return Cache::get("{$date}:{$from}:{$to}");
        } catch (Exception $e) {
            Log::error("Exchange Rate: {$e->getMessage()}");

            return null;
        }
    }
}
