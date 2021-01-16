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
    public function testIfADateFormatIsIncorrectReturnsNull(string $incorrect_date): void
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

    /**
     * @dataProvider exchangeRatesProvider
     */
    public function testCanGetExchangeRatesForTheSelectedDate(string $date, string $currency_name, string $expected_rate)
    {
        $erh = new ExchangeRatesHandler();

        $this->assertEquals($expected_rate, $erh->$currency_name($date));
    }

    public function exchangeRatesProvider(): array
    {
        return [
            'HKD on 2021-01-01' => ['2021-01-01', 'HKD', '9.53013'],
            'USD on 2021-01-01' => ['2021-01-01', 'USD', '73.8757'],
            'HKD on 2011-01-04' => ['2011-01-04', 'HKD', ''],
            'USD on 2011-01-04' => ['2011-01-04', 'USD', '30.3505'],
        ];
    }
}
