<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\Interfaces\SaveableToMysql;
use Chistowick\Lettuce\ExchangeRate;
use App\Models\Rate;
use Chistowick\Lettuce\Interfaces\ConvertibleToArray;
use Chistowick\Lettuce\Interfaces\SaveableToCache;

/**
 * The class for storing and processing a group of exchange rates.
 */
class ExchangeRatesGroup implements SaveableToMysql, ConvertibleToArray, SaveableToCache
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
     * Save to MySQL.
     *
     * @return void
     **/
    public function toMysql(): void
    {
        Rate::insert($this->toArray());
    }

    /**
     * Converts $group to array
     *
     * @return array
     **/
    public function toArray(): array
    {
        foreach ($this->group as $rate) {
            $group_array[] = $rate->toArray();
        }

        return $group_array;
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
}
