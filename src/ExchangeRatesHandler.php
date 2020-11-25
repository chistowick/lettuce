<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\Interfaces\SaveableToCache;

/**
 * The class for receiving and processing currency rates from the Central Bank of the Russian Federation API.
 */
class ExchangeRatesHandler
{
    /** @var int $expiration The cache expiration time, in seconds.*/
    protected $expiration = 60 * 60 * 24;

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
     * @return string|null Factor for converting $from to $to
     **/
    public function getFactor(string $from, string $to, string $date): ?string
    {
        return null;
    }

    /**
     * Saves a set of exchange rates in the cache.
     *
     * @param SaveableToCache $group A set of instances ExchangeRates to save in the cache.
     * @return void
     **/
    protected function saveToCache(SaveableToCache $group): void
    {
        $group->toCache($this->expiration);
    }
}
