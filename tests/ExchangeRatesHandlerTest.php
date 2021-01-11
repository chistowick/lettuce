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
            'month = 00' => ['2021-00-11'],
            'month = 13' => ['2021-13-01'],
            'day = 32' => ['2021-01-32'],
            'reverse the order' => ['11-01-2021'],
            'year = YY format' => ['21-01-11'],
            'day lost' => ['2021-01'],
            'a nonexistent day in a non-leap year' => ['2021-02-29'],
            'month lost' => ['2020--12'],
            'YYYY/MM/DD format' => ['2020/11/12']
        ];
    }
}
