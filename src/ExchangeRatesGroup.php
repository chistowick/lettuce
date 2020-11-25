<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\Interfaces\SaveableToMysql;
use Chistowick\Lettuce\ExchangeRate;
use App\Models\Rate;
use Chistowick\Lettuce\Interfaces\ConvertibleToArray;
use Chistowick\Lettuce\Interfaces\SaveableToCache;
use Illuminate\Support\Facades\Log;
use Exception;

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
     * @return bool
     **/
    public function toMysql(): bool
    {
        try {
            Rate::insert($this->toArray());

            return true;
        } catch (Exception $e) {
            Log::error("Exchange Rate: {$e->getMessage()}");

            return false;
        }
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
     * @return bool
     * @throws 
     **/
    public function toCache(int $expiration = null): bool
    {
        foreach ($this->group as $rate) {
            if (!$rate->toCache($expiration)) {
                return false;
            }
        }

        return true;
    }
}
