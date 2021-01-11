<?php

require __DIR__ . '/../vendor/autoload.php';

use Orchestra\Testbench\TestCase;
use Carbon\Carbon;
use Chistowick\Lettuce\ExchangeRatesHandler;

final class ExchangeRatesHandlerTest extends TestCase
{

    public function testCanNotGetTheExchangeRateForTheDayAfterTomorrow(): void
    {
        $day_after_tomorrow = Carbon::now()->addDays(2)->format("Y-m-d");
        $erh = new ExchangeRatesHandler();
        
        $this->assertNull($erh->usd($day_after_tomorrow));
    }
}
