<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\ExchangeRate;
use Chistowick\Lettuce\Interfaces\SaveableToCache;

/**
 * The class for storing and processing a group of exchange rates.
 */
class ExchangeRatesGroup implements SaveableToCache
{
    /** @var array $group Group of exchange rates instances */
    private $group = array();

    /**
     * Adds exchange rate to group
     *
     * @param ExchangeRate $rate Exchange rate object
     * @return void
     **/
    public function addRate(ExchangeRate $rate): void
    {
        $this->group[] = $rate;
    }

    /**
     * Saves the group to cache.
     *
     * @return void
     **/
    public function toCache(int $expiration = null): void
    {
        foreach ($this->group as $rate) {
            $rate->toCache($expiration);
        }
    }

    /**
     * Finds the factor value by the value '$from'
     *
     * @param string $from Source currency char-code
     * @return float|null
     **/
    public function findFactor(string $from): ?float
    {
        foreach ($this->group as $rate) {
            if ($rate->getFrom() === $from) {
                return $rate->getFactor();
            }
        }
    }
}
