<?php

use stvvt\TimeDeltaFormatter;
use PHPUnit\Framework\TestCase;

/**
 * TimeDeltaFormatter Test Case
 *
 */
class TimeDeltaFormatterTest extends TestCase
{

    protected function setUp()
    {
        TimeDeltaFormatter::$now = strtotime('2012-11-08 12:02:00');
    }

    protected static function s($s)
    {
        return TimeDeltaFormatter::$now + $s;
    }

    protected static function m($m, $s = 0)
    {
        return self::s($m * 60 + $s);
    }

    protected static function h($h, $m = 0, $s = 0)
    {
        return self::m($h * 60 + $m, $s);
    }

    protected static function d($d, $h = 0, $m = 0)
    {
        return self::h($d * 24 + $h, $m);
    }

    public function testNow()
    {
        // преди или след по-малко от една минута
        $this->assertEquals('сега', TimeDeltaFormatter::convert(self::s(50)));
        $this->assertEquals('сега', TimeDeltaFormatter::convert(self::s(-50)));
    }

    public function testMinutes()
    {
        $this->assertEquals('след минута', TimeDeltaFormatter::convert(self::s(51)));
        $this->assertEquals('преди минута', TimeDeltaFormatter::convert(self::s(-51)));
        $this->assertEquals('след минута', TimeDeltaFormatter::convert(self::m(1, 29)));
        $this->assertEquals('преди минута', TimeDeltaFormatter::convert(self::m(-1, -29)));
        $this->assertEquals('след минута', TimeDeltaFormatter::convert(self::m(1, 50)));
        $this->assertEquals('преди минута', TimeDeltaFormatter::convert(self::m(-1, -50)));
        $this->assertEquals('след 30 минути', TimeDeltaFormatter::convert(self::m(26)));
        $this->assertEquals('преди 30 минути', TimeDeltaFormatter::convert(self::m(-26)));
        $this->assertEquals('след 35 минути', TimeDeltaFormatter::convert(self::m(35)));
        $this->assertEquals('след 50 минути', TimeDeltaFormatter::convert(self::m(50)));
        $this->assertEquals('преди 50 минути', TimeDeltaFormatter::convert(self::m(-50)));
    }

    public function testHoursToday()
    {
        $this->assertEquals('след 30 минути', TimeDeltaFormatter::convert(self::m(26)));
        $this->assertEquals('след 30 минути', TimeDeltaFormatter::convert(self::m(34)));
        $this->assertEquals('преди 30 минути', TimeDeltaFormatter::convert(self::m(-26)));
        $this->assertEquals('преди 30 минути', TimeDeltaFormatter::convert(self::m(-34)));

        // До +/- 1 час и 10 мин се закръгля на 1 час
        $this->assertEquals('след час', TimeDeltaFormatter::convert(self::m(55, 1)));
        $this->assertEquals('преди час', TimeDeltaFormatter::convert(self::m(-55, -1)));
        $this->assertEquals('преди час', TimeDeltaFormatter::convert(self::h(-1, -5, 1)));

        $this->assertEquals('след час и 15 мин.', TimeDeltaFormatter::convert(self::h(1, 15)));
        $this->assertEquals('преди час и 15 мин.', TimeDeltaFormatter::convert(self::h(-1, -15)));
        $this->assertEquals('след час и 30 мин.', TimeDeltaFormatter::convert(self::h(1, 26)));
        $this->assertEquals('след час и 30 мин.', TimeDeltaFormatter::convert(self::h(1, 34)));
        $this->assertEquals('преди час и 30 мин.', TimeDeltaFormatter::convert(self::h(-1, -26)));
        $this->assertEquals('преди час и 30 мин.', TimeDeltaFormatter::convert(self::h(-1, -34)));
        $this->assertEquals('след час и 35 мин.', TimeDeltaFormatter::convert(self::h(1, 35)));
        $this->assertEquals('преди час и 35 мин.', TimeDeltaFormatter::convert(self::h(-1, -35)));

        $this->assertEquals('след 2 часа', TimeDeltaFormatter::convert(self::h(1, 55)));
        $this->assertEquals('преди 2 часа', TimeDeltaFormatter::convert(self::h(-1, -55)));
        $this->assertEquals('след 2 часа и 15 мин.', TimeDeltaFormatter::convert(self::h(2, 15)));
        $this->assertEquals('преди 2 часа и 15 мин.', TimeDeltaFormatter::convert(self::h(-2, -15)));
        $this->assertEquals('след 2 часа и 30 мин.', TimeDeltaFormatter::convert(self::h(2, 26)));
        $this->assertEquals('след 2 часа и 30 мин.', TimeDeltaFormatter::convert(self::h(2, 34)));
        $this->assertEquals('преди 2 часа и 30 мин.', TimeDeltaFormatter::convert(self::h(-2, -26)));
        $this->assertEquals('преди 2 часа и 30 мин.', TimeDeltaFormatter::convert(self::h(-2, -34)));
        $this->assertEquals('след 2 часа и 35 мин.', TimeDeltaFormatter::convert(self::h(2, 35)));
        $this->assertEquals('преди 2 часа и 35 мин.', TimeDeltaFormatter::convert(self::h(-2, -35)));

        // Бъдещ момент от същия ден на повече от 8 часа
        $this->assertEquals('днес в 23:02 ч.', TimeDeltaFormatter::convert(self::h(11)));

        // Минал момент от същия ден
        $this->assertEquals('днес в 01:02 ч.', TimeDeltaFormatter::convert(self::h(-11)));
    }

    public function testHours()
    {
        TimeDeltaFormatter::$now = strtotime('2012-11-08 20:00');

        // Бъдещ момент на другия ден, но след по-малко от 6 часа
        $this->assertEquals('след 6 часа', TimeDeltaFormatter::convert(self::h(6)));

        // Бъдещ момент на другия ден, но след повече от 6 часа
        $this->assertEquals('утре в 06:20 ч.', TimeDeltaFormatter::convert(self::h(10, 20)));

        TimeDeltaFormatter::$now = strtotime('2012-11-08 4:00');

        // Минал момент на предишния ден, но преди по-малко от 6 часа
        $this->assertEquals('преди 6 часа', TimeDeltaFormatter::convert(self::h(-6)));

        // Минал момент на предишния ден, но преди повече от 6 часа
        $this->assertEquals('вчера в 18:00 ч.', TimeDeltaFormatter::convert(self::h(-10)));

    }

    public function testThisMonth()
    {
        $this->assertEquals('след 2 дни', TimeDeltaFormatter::convert(self::d(2)));
        $this->assertEquals('преди 2 дни', TimeDeltaFormatter::convert(self::d(-2)));

    }

    public function testDaysDelta()
    {
        TimeDeltaFormatter::$now = strtotime('2012-11-08 00:00');

        $this->assertEquals('след 5 дни', TimeDeltaFormatter::convert('2012-11-13 16:00'));
        $this->assertEquals('след 5 дни', TimeDeltaFormatter::convert('2012-11-13 23:00'));
    }

    /**
     * Когато делтата е под 1 година, над 1 месец и близо до точен брой месеци (+-5 дни)
     */
    public function testNearlyMonths()
    {
        TimeDeltaFormatter::$now = strtotime('2013-01-17 12:01');

        $this->assertEquals('преди близо 2 месеца', TimeDeltaFormatter::convert('2012-11-17 00:00'));
    }

    public function testYesterday()
    {
        TimeDeltaFormatter::$now = strtotime('2013-02-17 16:26:00');

        $this->assertEquals('вчера в 14:00 ч.', TimeDeltaFormatter::convert('2013-02-16 14:00:00'));
    }
}
