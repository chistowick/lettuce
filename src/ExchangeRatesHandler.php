<?php

namespace Chistowick\Lettuce;

/**
 * The class for receiving and processing currency rates from the Central Bank of the Russian Federation API.
 */
class ExchangeRatesHandler
{
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
     * Carries out sequential search in the cache, in the database, in the API of the Central Bank of the Russian Federation;
     * Automatically tries to save the received data in the previous search locations;
     * In case of impossibility to receive data even through the API or in case of unrecoverable problems, returns null;
     * Writes logs. See configuration;
     *
     * @param string $from Source currency letter code
     * @param string $to Destination currency letter code
     * @param string $date Date (YYYY-MM-DD)
     * @return string|null Factor for converting $from to $to
     **/
    public function getFactor(string $from, string $to, string $date):?string
    {
        return null;
    }
}