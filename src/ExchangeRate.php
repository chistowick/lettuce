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
    private $to;
    private $factor;
    private $date;

    public function __construct(string $from, string $to, float $factor, string $date)
    {
        $this->from = $from;
        $this->to = $to;
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
        $key  = "{$this->date}:{$this->from}:{$this->to}";
        $value = $this->factor;

        $arr = array($key, $value);
        $expiration ? ($arr[] = $expiration) : null;

        Cache::put(...$arr);
    }
}
