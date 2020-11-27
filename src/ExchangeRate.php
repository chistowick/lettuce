<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\Interfaces\SaveableToCache;
use Illuminate\Support\Facades\Cache;

/**
 * The class for the storing and handling of exchange rates.
 */
class ExchangeRate implements SaveableToCache
{
    private $from;
    private $factor;
    private $date;

    public function __construct(string $from, float $factor, string $date)
    {
        $this->from = $from;
        $this->factor = $factor;
        $this->date = $date;
    }

    /**
     * Save to cache.
     *
     * @return void
     **/
    public function toCache(int $expiration = null): void
    {
        $key  = "{$this->date}:{$this->from}";
        $value = $this->factor;

        $arr = array($key, $value);
        $expiration ? ($arr[] = $expiration) : null;

        Cache::put(...$arr);
    }
}
