# Lettuce

### Description

The package for Laravel. For receiving and processing currency rates from the Central Bank of the Russian Federation API.
***
### Installation

#### With Composer

```
$ composer require chistowick/lettuce
```
***
### Demonstration
```php
<?php

    use Chistowick\Lettuce\ExchangeRatesHandler;

    $erh = new ExchangeRatesHandler();

    // Getting the exchange rate relative to the RUB:
    $erh->usd();
    $erh->eur();

    // Getting a multiplier for converting one currency to another:
    $erh->usdToEur();

    // It will also work
    $erh->USD();
    $erh->USDToEUR();

    // By default, for today:
    echo "USD = {$erh->usd()} RUB";
    echo "EUR = {$erh->eur()} RUB";

    echo "USD to EUR = {$erh->usdToEur()}";

    // or...

    // On the selected date in the YYYY-MM-DD format:
    echo "USD = {$erh->usd('2020-02-29')} RUB";
    echo "EUR = {$erh->eur('2020-02-29')} RUB";

    echo "USD to EUR = {$erh->usdToEur('2020-02-29')}";

    // If you try to get nonexistent data about courses, null is returned.
    $erh->usd('2099-02-29');
```
#### Additionally
    When trying to get data about courses for tomorrow: 
        1. if the course has not been published yet, null will be returned. 
        2. if the course is already published, you will receive it.
***
### Cache
    
The received courses will be cached according to the default driver value in the configuration of your Laravel application.
***
### Logging
    
The program logs exceptions and key points according to the default channel value in the configuration of your Laravel application.
***
### Configuration
The list of downloadable currencies is available in the package by the path: lettuce/config/codes.php
