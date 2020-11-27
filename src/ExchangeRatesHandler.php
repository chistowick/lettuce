<?php

namespace Chistowick\Lettuce;

use Chistowick\Lettuce\Interfaces\SaveableToCache;
use Chistowick\Lettuce\ExchangeRatesGroup;
use Chistowick\Lettuce\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * The class for receiving and processing currency rates from the Central Bank of the Russian Federation API.
 */
class ExchangeRatesHandler
{
    /** @var int $expiration The cache expiration time, in seconds.*/
    private $expiration = 60 * 60 * 24;

    /** @var array $codes The set of currency codes.*/
    protected $codes = [
        'USD' => 'R01235',
        'EUR' => 'R01239',
    ];

    /**
     * Interceptor method for creating short methods for fast currency conversion.
     *
     * @param string $method Method name
     * @param array $args Arguments (supposed only $args[0]  - date YYYY-MM-DD (by default = null))
     * @return float|null
     **/
    public function __call(string $method, array $args): ?float
    {
        preg_match('~^[A-Za-z]{3}To[A-Za-z]{3}$~', $method, $matches);

        if ($matches) {

            $currencies = explode('To', $matches[0]);

            $from = strtoupper($currencies[0]);
            $to = strtoupper($currencies[1]);
            $date = isset($args[0]) ? $args[0] : null;

            return $this->convert($from, $to, $date);
        }

        preg_match('~^[A-Za-z]{3}$~', $method, $matches);

        if ($matches) {
            $from = strtoupper($matches[0]);
            $date = isset($args[0]) ? $args[0] : null;

            return $this->getRateToRub($from, $date);
        }
    }

    /**
     * Returns the value of the coefficient for converting one currency to another on the selected date;
     *
     * @param string $from Source currency char-code
     * @param string $to Destination currency char-code
     * @param string|null $date Date (YYYY-MM-DD)
     * @return float|null Factor for converting $from to $to
     **/
    protected function convert(string $from, string $to, ?string $date): ?float
    {
        if ($from == $to) {
            return (float)1;
        }

        $dividend = $this->getRateToRub($from, $date);
        $divider = $this->getRateToRub($to, $date);

        if (!isset($dividend, $divider) || ($divider == 0)) {
            return null;
        }

        return $dividend / $divider;
    }

    /**
     * Returns the value of the exchange rate against the ruble on the selected date;
     *
     * @param string $from Source currency char-code
     * @param string|null $date Date (YYYY-MM-DD)
     * @return float|null Exchange rate
     **/
    protected function getRateToRub(string $from, ?string $date): ?float
    {
        try {

            if ($from == "RUB") {
                return (float)1;
            }

            // Inputted $from resolving.
            if (!isset($this->codes[$from])) {
                return null;
            }

            // Inputted date resolving.
            $date = $this->dateResolve($date);
            if (!isset($date)) {
                return null;
            }

            // Search for data in the cache.
            $data_cache = $this->checkCache($from, $date);

            if (isset($data_cache)) {
                return (float) $data_cache;
            }

            // Attempt to get data from the API of the Central Bank of the Russian Federation
            $data_api = $this->checkApi($date);

            if (isset($data_api)) {
                $this->saveToCache($data_api);

                return $data_api->findFactor($from);
            } else {
                return null;
            }
        } catch (Exception $e) {
            Log::error("ExchangeRatesHandler: Unexpectedly... {$e->getMessage()}");

            return null;
        }
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
            Log::error("ExchangeRatesHandler: {$e->getMessage()}");
        }
    }

    /**
     * Search for data in the cache.
     *
     * @param string $from Source currency char-code
     * @param string $date Date (YYYY-MM-DD)
     * @return string|null Value of the exchange rate from the cache
     **/
    protected function checkCache(string $from, string $date): ?string
    {
        try {
            return Cache::get("{$date}:{$from}");
        } catch (Exception $e) {
            Log::error("ExchangeRatesHandler: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Getting currency exchange rates from the Central Bank of the Russian Federation API.
     *
     * @param string $date Date (YYYY-MM-DD)
     * @return ExchangeRatesGroup|null ExchangeRate instance group
     **/
    protected function checkApi(string $date): ?ExchangeRatesGroup
    {
        try {
            $start = Carbon::createFromDate($date)->subDays(20)->format('d/m/Y');
            $end = Carbon::createFromDate($date)->format('d/m/Y');

            $group = new ExchangeRatesGroup();

            foreach ($this->setUrls($start, $end) as $char_code => $url) {

                $xml = simplexml_load_file($url);

                if (!isset($xml->Record)) {
                    throw new Exception("The API response is empty.");
                }

                $last_value = str_replace(',', '.', $xml->xpath("/ValCurs/Record[last()]/Value"));
                $last_nominal = $xml->xpath("/ValCurs/Record[last()]/Nominal");

                if (!preg_match("/^[0-9]+\.[0-9]+$/", $last_value)) {
                    throw new Exception("Invalid format of the exchange rate value from the api.");
                }

                if (($last_nominal == 0) || !is_numeric($last_nominal)) {
                    throw new Exception("Invalid format of the nominal value from the api.");
                }

                $factor = ((float)$last_value) / $last_nominal;
                $group->addRate(new ExchangeRate($char_code, $factor, $date));
            }
            Log::info("ExchangeRatesHandler: The exchange rate for '{$date}' was loaded successfully.");
            return $group;
        } catch (Exception $e) {
            Log::error("ExchangeRatesHandler: Failed to load the exchange rate on '{$date}'. {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Generates a set of URLs for requests.
     *
     * @param string $start Start of the range.
     * @param string $end End of the range.
     * @return iterable A set of URLs for requests.
     **/
    protected function setUrls(string $start, string $end): iterable
    {
        foreach ($this->codes as $char_code => $id) {
            yield  $char_code => "http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1={$start}&date_req2={$end}&VAL_NM_RQ={$id}";
        }
    }

    /**
     * Checks the date.
     *
     * @param string|null $date Date to check
     * @return string|null Correct date
     **/
    protected function dateResolve(?string $date): ?string
    {
        try {
            if ($date) {
                $pieces = explode('-', $date);
                $time_point = Carbon::createSafe((int)$pieces[0], (int)$pieces[1], (int)$pieces[2]);

                if ($time_point->isFuture()) {
                    $today = Carbon::today();
                    $interval = $time_point->diffInDays($today);

                    if ($interval > 1) {
                        throw new Exception("'{$time_point->format("Y-m-d")}' - is incorrect date.");
                    }
                }
                return $time_point->format("Y-m-d");
            } else {
                return Carbon::now()->format("Y-m-d"); // default
            }
        } catch (Exception $e) {
            Log::error("ExchangeRatesHandler: The inputted date is incorrect. {$e->getMessage()}");

            return null;
        }
    }
}
