<?php

require __DIR__ . '/../vendor/autoload.php';

use Orchestra\Testbench\TestCase;
use Carbon\Carbon;
use Chistowick\Lettuce\ExchangeRatesHandler;

final class ExchangeRatesHandlerTest extends TestCase
{

    public function testCannotGetTheExchangeRateForTheDayAfterTomorrow(): void
    {
        $day_after_tomorrow = Carbon::now()->addDays(2)->format("Y-m-d");
        $erh = new ExchangeRatesHandler();

        $this->assertNull($erh->usd($day_after_tomorrow));
    }

    /**
     * @dataProvider incorrectDatesProvider
     */
    public function testCannotBeUsedWithIncorrectDateFormat(string $incorrect_date): void
    {
        $erh = new ExchangeRatesHandler();

        $this->assertNull($erh->usd($incorrect_date));
    }

    public function incorrectDatesProvider(): array
    {
        return [
            ['2021-00-11'],
            ['2021-13-01'],
            ['2021-01-32'],
            ['11-01-2021'],
            ['21-01-11'],
            ['2021-01'],
            ['2021-02-29'],
            ['2020--12'],
            ['2020/11/12']
        ];
    }
}
