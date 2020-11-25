<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\Interfaces\ConvertibleToArray;
use Chistowick\Lettuce\Interfaces\SaveableToCache;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * The class for the storing and handling of exchange rates.
 */
class ExchangeRate implements SaveableToCache, ConvertibleToArray
{
    private $date;
    private $real_date;
    private $from;
    private $to;
    private $factor;


    public function __construct(string $from, string $to, string $factor, string $date, string $real_date)
    {
        $this->from = $from;
        $this->to = $to;
        $this->factor = $factor;
        $this->date = $date;
        $this->real_date = $real_date;
    }

    /**
     * Save to cache.
     *
     * @return bool
     * @throws 
     **/
    public function toCache(int $expiration = null): bool
    {
        try {
            $key  = "{$this->date}:{$this->from}:{$this->to}";
            $value = $this->factor;

            $arr = array($key, $value);
            $expiration ? ($arr[] = $expiration) : null;

            Cache::put(...$arr);

            return true;
        } catch (Exception $e) {

            Log::error("Exchange Rate: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Converts exchange rate instance to array
     *
     * @return array
     **/
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'from' => $this->from,
            'to' => $this->to,
            'factor' => $this->factor,
            'real_date' => $this->real_date,
        ];
    }
}
