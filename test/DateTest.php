<?php

/** @noinspection DuplicatedCode */

use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\DateSet;
use MadisonSolutions\JustDate\DayOfWeek;
use MadisonSolutions\JustDate\JustDate;
use MadisonSolutions\JustDate\JustTime;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    /**
     * Helper method for verifying the expected exception is thrown when the callback is executed
     *
     * @param  class-string<object>  $exceptionClass
     */
    protected function assertThrows(string $exceptionClass, callable $callback): void
    {
        $e = null;
        try {
            $callback();
        } catch (Exception $e) {
        }
        $this->assertInstanceOf($exceptionClass, $e);
    }

    protected function assertJustDate(string $expectedYmd, mixed $actual): void
    {
        $this->assertInstanceOf(JustDate::class, $actual);
        $this->assertSame($expectedYmd, (string) $actual);
    }

    protected function assertDateRange(string $expectedYmd, mixed $actual): void
    {
        $this->assertInstanceOf(DateRange::class, $actual);
        $this->assertSame($expectedYmd, (string) $actual);
    }

    public function test_create_just_dates(): void
    {
        $d = JustDate::make(2019, 4, 21);
        $this->assertJustDate('2019-04-21', $d);
    }

    public function test_create_from_date_time(): void
    {
        // Create a PHP DateTime object with the given date, time and timezone
        /** @noinspection PhpUnhandledExceptionInspection */
        $p1 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Australia/Sydney'));

        // The resulting JustDate object should have the matching date with no time or timezone info
        $d1 = JustDate::fromDateTime($p1);
        $this->assertJustDate('2019-04-21', $d1);
        $this->assertSame(gmmktime(0, 0, 0, 4, 21, 2019), $d1->timestamp);

        // A DateTime with the same date and time in a different timezone will naturally have a different timestamp
        // Since it refers to a different instance in time
        /** @noinspection PhpUnhandledExceptionInspection */
        $p2 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Asia/Calcutta'));
        $this->assertNotEquals($p1->getTimestamp(), $p2->getTimestamp());

        // ...however it should produce the same JustDate object
        $d2 = JustDate::fromDateTime($p2);
        $this->assertJustDate('2019-04-21', $d2);
        $this->assertSame($d1->timestamp, $d2->timestamp);
        $this->assertTrue($d1->isSameAs($d2));
    }

    public function test_create_today(): void
    {
        $d1 = JustDate::today();
        $this->assertJustDate(date('Y-m-d'), $d1);

        $d2 = JustDate::today(new DateTimeZone('UTC'));
        $this->assertJustDate(gmdate('Y-m-d'), $d2);

        // These Pacific timezones overlap each other, so it's never the same date in both
        $d3 = JustDate::today(new DateTimeZone('Pacific/Kiritimati'));
        $d4 = JustDate::today(new DateTimeZone('Pacific/Niue'));
        $this->assertNotEquals((string) $d3, (string) $d4);

        $d5 = JustDate::yesterday();
        $this->assertJustDate(date('Y-m-d', strtotime('yesterday')), $d5);

        $d6 = JustDate::tomorrow();
        $this->assertJustDate(date('Y-m-d', strtotime('tomorrow')), $d6);
    }

    public function test_create_from_timestamp(): void
    {
        // Create the timestamp for 2019-04-21 16:23 in UTC
        $ts = gmmktime(16, 23, 12, 4, 21, 2019);
        assert(is_int($ts));

        // Create a JustDate from the timestamp
        $d1 = JustDate::fromTimestamp($ts);
        $this->assertJustDate('2019-04-21', $d1);

        // Same timestamp but with a timezone explicitly set
        $d2 = JustDate::fromTimestamp($ts, new DateTimeZone('Europe/London'));
        $this->assertJustDate('2019-04-21', $d2);

        // At the same time in Sydney, it will already be the 22nd April
        $d3 = JustDate::fromTimestamp($ts, new DateTimeZone('Australia/Sydney'));
        $this->assertJustDate('2019-04-22', $d3);
    }

    public function test_create_from_ymd(): void
    {
        $d1 = JustDate::fromYmd('2019-04-21');
        $this->assertJustDate('2019-04-21', $d1);
    }

    public function test_cannot_create_from_invalid_ymd(): void
    {
        $this->assertThrows(InvalidArgumentException::class, function () {
            JustDate::fromYmd('foo');
        });

        $this->assertThrows(InvalidArgumentException::class, function () {
            JustDate::fromYmd('19-04-21');
        });

        $this->assertThrows(InvalidArgumentException::class, function () {
            JustDate::fromYmd('2019-21-04');
        });
    }

    public function test_epoch_day(): void
    {
        $this->assertJustDate('1970-01-01', JustDate::fromEpochDay(0));
        $this->assertJustDate('1970-01-02', JustDate::fromEpochDay(1));
        $this->assertJustDate('1969-12-31', JustDate::fromEpochDay(-1));
        $this->assertEquals(0, JustDate::fromYmd('1970-01-01')->epoch_day);
        $this->assertEquals(1, JustDate::fromYmd('1970-01-02')->epoch_day);
        $this->assertEquals(-1, JustDate::fromYmd('1969-12-31')->epoch_day);
        $this->assertEquals(strtotime('2021-04-28T00:00:00.03+00:00') / (60 * 60 * 24), JustDate::fromYmd('2021-04-28')->epoch_day);
    }

    public function testdifference(): void
    {
        $this->assertSame(2, JustDate::difference(JustDate::make(2019, 04, 21), JustDate::make(2019, 04, 23)));
        $this->assertSame(0, JustDate::difference(JustDate::make(2019, 04, 21), JustDate::make(2019, 04, 21)));
        $this->assertSame(-2, JustDate::difference(JustDate::make(2019, 04, 23), JustDate::make(2019, 04, 21)));
        $this->assertSame(365, JustDate::difference(JustDate::make(2018, 04, 21), JustDate::make(2019, 04, 21)));
    }

    public function test_getters(): void
    {
        $d = JustDate::make(2019, 04, 21);
        $this->assertSame(2019, $d->year);
        $this->assertSame(4, $d->month);
        $this->assertSame(21, $d->day);
        $this->assertSame(DayOfWeek::Sunday, $d->day_of_week);
        $this->assertSame(gmmktime(0, 0, 0, 4, 21, 2019), $d->timestamp);
    }

    public function test_add_days(): void
    {
        $d1 = JustDate::make(2019, 04, 21);
        $this->assertJustDate('2019-04-22', $d1->addDays(1));
        $this->assertJustDate('2019-04-22', $d1->nextDay());
        $this->assertJustDate('2019-05-01', $d1->addDays(10));
        $this->assertJustDate('2020-02-15', $d1->addDays(300));
        $this->assertJustDate('2019-04-20', $d1->addDays(-1));
        $this->assertJustDate('2019-04-20', $d1->prevDay());
        $this->assertJustDate('2018-06-25', $d1->addDays(-300));

        $this->assertJustDate('2019-04-28', $d1->addWeeks(1));
        $this->assertJustDate('2019-05-05', $d1->addWeeks(2));
        $this->assertJustDate('2019-04-14', $d1->addWeeks(-1));

        $this->assertJustDate('2019-05-21', $d1->addMonths(1));
        $this->assertJustDate('2019-06-21', $d1->addMonths(2));
        $this->assertJustDate('2019-03-21', $d1->addMonths(-1));

        $this->assertJustDate('2020-04-21', $d1->addYears(1));
        $this->assertJustDate('2021-04-21', $d1->addYears(2));
        $this->assertJustDate('2018-04-21', $d1->addYears(-1));

        $this->assertJustDate('2020-05-22', $d1->add(1, 1, 1));
        $this->assertJustDate('2019-04-21', $d1->add(0, 0, 0));
        $this->assertJustDate('2018-03-20', $d1->add(-1, -1, -1));

        // Check some edge cases
        // Adding one month to Jan 30 is ambiguous because there is no Feb 30
        // Expect it to overflow to March 1/2 (depending on if it's a leap year)
        $this->assertJustDate('2020-03-01', (JustDate::make(2020, 01, 30))->addMonths(1));
        $this->assertJustDate('2021-03-02', (JustDate::make(2021, 01, 30))->addMonths(1));
        $this->assertJustDate('2021-03-30', (JustDate::make(2021, 01, 30))->addMonths(2));
        $this->assertJustDate('2021-03-02', (JustDate::make(2021, 03, 30))->addMonths(-1));
        // Note this means sometimes $d->addMonths(a)->addMonths(b) is not equal to $d->addMonths(a + b) !
    }

    public function test_sub_days(): void
    {
        $d1 = JustDate::make(2019, 04, 21);
        $this->assertJustDate('2019-04-20', $d1->subDays(1));
        $this->assertJustDate('2019-04-11', $d1->subDays(10));
        $this->assertJustDate('2018-06-25', $d1->subDays(300));
        $this->assertJustDate('2019-04-22', $d1->subDays(-1));
        $this->assertJustDate('2020-02-15', $d1->subDays(-300));

        $this->assertJustDate('2019-04-14', $d1->subWeeks(1));
        $this->assertJustDate('2019-03-31', $d1->subWeeks(3));
        $this->assertJustDate('2019-04-28', $d1->subWeeks(-1));

        $this->assertJustDate('2019-03-21', $d1->subMonths(1));
        $this->assertJustDate('2019-02-21', $d1->subMonths(2));
        $this->assertJustDate('2019-05-21', $d1->subMonths(-1));

        $this->assertJustDate('2018-04-21', $d1->subYears(1));
        $this->assertJustDate('2017-04-21', $d1->subYears(2));
        $this->assertJustDate('2020-04-21', $d1->subYears(-1));

        // Check some edge cases
        // Subtracting one month from Mar 30 is ambiguous because there is no Feb 30
        // Expect it to overflow to March 1/2 (depending on if it's a leap year)
        $this->assertJustDate('2020-03-01', (JustDate::make(2020, 03, 30))->subMonths(1));
        $this->assertJustDate('2021-03-02', (JustDate::make(2021, 03, 30))->subMonths(1));
        $this->assertJustDate('2021-01-30', (JustDate::make(2021, 03, 30))->subMonths(2));
    }

    public function test_format(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $p1 = new DateTime('2019-04-21 16:23:12', new DateTimeZone('Australia/Sydney'));
        $d1 = JustDate::fromDateTime($p1);

        $this->assertSame('Sun April 21st 2019', $d1->format('D F jS Y'));
        $this->assertSame('Sun, 21 Apr 2019 00:00:00 +0000', $d1->format('r'));

        $d2 = $d1->addDays(3);
        $this->assertSame('Wed, 24 Apr 2019 00:00:00 +0000', $d2->format('r'));
    }

    public function test_comparisons(): void
    {
        $d1 = JustDate::make(2019, 04, 21);
        $d2 = JustDate::make(2019, 04, 22);

        $this->assertTrue($d1->isBefore($d2));
        $this->assertFalse($d2->isBefore($d1));
        $this->assertTrue($d2->isAfter($d1));
        $this->assertFalse($d1->isAfter($d2));
        $this->assertTrue($d1->isBeforeOrSameAs($d1));
        $this->assertTrue($d1->isBeforeOrSameAs($d2));
        $this->assertFalse($d2->isBeforeOrSameAs($d1));
        $this->assertTrue($d2->isAfterOrSameAs($d2));
        $this->assertTrue($d2->isAfterOrSameAs($d1));
        $this->assertFalse($d1->isAfterOrSameAs($d2));

        $this->assertTrue($d1->isSameAs(JustDate::fromYmd('2019-04-21')));
    }

    public function test_date_trickery(): void
    {
        $d1 = JustDate::make(2019, 04, 0); // day = 0 gives last day of prev month
        $this->assertJustDate('2019-03-31', $d1);

        $d2 = JustDate::make(2019, 1, 0);
        $this->assertJustDate('2018-12-31', $d2);
    }

    public function test_start_and_end_of_months(): void
    {
        $d1 = JustDate::make(2019, 04, 21);
        $this->assertJustDate('2019-04-01', $d1->startOfMonth());
        $this->assertJustDate('2019-04-30', $d1->endOfMonth());
    }

    public function test_start_and_end_of_weeks(): void
    {
        $d1 = JustDate::make(2024, 8, 2); // Friday
        $this->assertJustDate('2024-07-29', $d1->startOfWeek()); // Monday
        $this->assertJustDate('2024-08-04', $d1->endOfWeek()); // Sunday

        // Take Sunday as the 'first' day of the week
        $this->assertJustDate('2024-07-28', $d1->startOfWeek(week_starts_on: DayOfWeek::Sunday)); // Sunday
        $this->assertJustDate('2024-08-03', $d1->endOfWeek(week_starts_on: DayOfWeek::Sunday)); // Saturday

        // Make sure it still works when start/end is the same date
        $d2 = JustDate::make(2024, 7, 29); // Monday
        $this->assertJustDate('2024-07-29', $d2->startOfWeek()); // Monday
        $d3 = JustDate::make(2024, 8, 4); // Sunday
        $this->assertJustDate('2024-08-04', $d3->endOfWeek()); // Sunday
    }

    public function test_earliest_and_latest(): void
    {
        $d1 = JustDate::make(2019, 04, 21);
        $this->assertJustDate('2019-04-21', JustDate::earliest($d1));
        $this->assertJustDate('2019-04-21', JustDate::latest($d1));
        $this->assertJustDate('2019-04-21', JustDate::earliest($d1, $d1));
        $this->assertJustDate('2019-04-21', JustDate::latest($d1, $d1));

        $d2 = JustDate::make(2019, 04, 22);
        $this->assertJustDate('2019-04-21', JustDate::earliest($d1, $d2));
        $this->assertJustDate('2019-04-22', JustDate::latest($d1, $d2));

        $d3 = JustDate::make(2019, 04, 23);
        $this->assertJustDate('2019-04-21', JustDate::earliest($d3, $d2, $d1));
        $this->assertJustDate('2019-04-23', JustDate::latest($d3, $d2, $d1));
    }

    public function test_days_of_the_week(): void
    {
        $d0 = JustDate::make(2021, 03, 01); // Monday 1st March
        for ($i = 0; $i < 7; $i++) {
            $d = $d0->addDays($i);
            $is_days = [
                $d->isMonday(),
                $d->isTuesday(),
                $d->isWednesday(),
                $d->isThursday(),
                $d->isFriday(),
                $d->isSaturday(),
                $d->isSunday(),
            ];
            for ($j = 0; $j < 7; $j++) {
                if ($i == $j) {
                    $this->assertTrue($is_days[$j]);
                } else {
                    $this->assertFalse($is_days[$j]);
                }
            }
            if ($d->isSaturday() || $d->isSunday()) {
                $this->assertTrue($d->isWeekend());
                $this->assertFalse($d->isWeekday());
            } else {
                $this->assertFalse($d->isWeekend());
                $this->assertTrue($d->isWeekday());
            }
        }
    }

    public function test_conversion_to_date_time(): void
    {
        $default_timezone = new DateTimeZone(date_default_timezone_get());
        $tahiti = new DateTimeZone('Pacific/Tahiti');

        $d1 = JustDate::make(2021, 04, 28);

        // With no parameters, we should get midnight, in the system default timezone
        $td = $d1->toDateTime();
        $this->assertEquals('2021-04-28 00:00:00', $td->format('Y-m-d H:i:s'));
        $this->assertEquals($default_timezone, $td->getTimezone());

        // Try specifying a time
        $td = $d1->toDateTime(JustTime::fromHis('14:35:02'));
        $this->assertEquals('2021-04-28 14:35:02', $td->format('Y-m-d H:i:s'));
        $this->assertEquals($default_timezone, $td->getTimezone());

        // Try specifying a timezone
        $td = $d1->toDateTime(null, $tahiti);
        $this->assertEquals('2021-04-28 00:00:00', $td->format('Y-m-d H:i:s'));
        $this->assertEquals($tahiti, $td->getTimezone());

        // Try specifying both
        $td = $d1->toDateTime(JustTime::fromHis('14:35:02'), $tahiti);
        $this->assertEquals('2021-04-28 14:35:02', $td->format('Y-m-d H:i:s'));
        $this->assertEquals($tahiti, $td->getTimezone());
    }

    public function test_at_time(): void
    {
        $default_timezone = new DateTimeZone(date_default_timezone_get());
        $tahiti = new DateTimeZone('Pacific/Tahiti');

        $d1 = JustDate::make(2021, 04, 28);
        $t1 = JustTime::fromHis('14:35:02');

        // atTime() is an alias for toDateTime() with the time required
        $td = $d1->atTime($t1);
        $this->assertEquals('2021-04-28 14:35:02', $td->format('Y-m-d H:i:s'));
        $this->assertEquals($default_timezone, $td->getTimezone());
        $this->assertEquals($d1->toDateTime($t1), $td);

        $td = $d1->atTime($t1, $tahiti);
        $this->assertEquals('2021-04-28 14:35:02', $td->format('Y-m-d H:i:s'));
        $this->assertEquals($tahiti, $td->getTimezone());
        $this->assertEquals($d1->toDateTime($t1, $tahiti), $td);

        // Should be the complement of JustTime::onDate()
        $this->assertEquals($t1->onDate($d1, $tahiti), $td);
    }

    public function test_compare(): void
    {
        $jan1 = JustDate::fromYmd('2024-01-01');
        $jan2 = JustDate::fromYmd('2024-01-02');

        $this->assertEquals(-1, JustDate::compare($jan1, $jan2));
        $this->assertEquals(0, JustDate::compare($jan1, $jan1));
        $this->assertEquals(1, JustDate::compare($jan2, $jan1));

        $dates = [
            JustDate::fromYmd('2024-10-20'),
            JustDate::fromYmd('2024-03-17'),
            JustDate::fromYmd('2024-01-01'),
            JustDate::fromYmd('2024-10-20'),
            JustDate::fromYmd('2024-12-31'),
        ];
        $ordered = '2024-01-01, 2024-03-17, 2024-10-20, 2024-10-20, 2024-12-31';
        $reverse_ordered = '2024-12-31, 2024-10-20, 2024-10-20, 2024-03-17, 2024-01-01';

        $dates_copy_1 = $dates;
        usort($dates_copy_1, [JustDate::class, 'compare']);
        $this->assertEquals($ordered, implode(', ', $dates_copy_1));

        $dates_copy_2 = $dates;
        usort($dates_copy_2, fn ($a, $b) => JustDate::compare($a, $b));
        $this->assertEquals($ordered, implode(', ', $dates_copy_2));

        $dates_copy_3 = $dates;
        usort($dates_copy_3, fn ($a, $b) => -JustDate::compare($a, $b));
        $this->assertEquals($reverse_ordered, implode(', ', $dates_copy_3));
    }

    public function test_create_range(): void
    {
        $d1 = JustDate::make(2019, 04, 21);
        $d2 = JustDate::make(2019, 04, 25);
        $r = DateRange::make($d1, $d2);
        $this->assertDateRange('2019-04-21 to 2019-04-25', $r);

        $r = DateRange::fromYmd('2019-04-21', '2019-04-25');
        $this->assertDateRange('2019-04-21 to 2019-04-25', $r);

        // Won't let you make a range with end before start
        $this->assertThrows(InvalidArgumentException::class, function () {
            DateRange::make(JustDate::make(2019, 04, 21), JustDate::make(2019, 04, 19));
        });
        $this->assertThrows(InvalidArgumentException::class, function () {
            DateRange::fromYmd('2019-04-21', '2019-04-19');
        });

        // Start same as end is allowed though
        $r = DateRange::make($d1, $d1);
        $this->assertDateRange('2019-04-21 to 2019-04-21', $r);

        // You can use eitherWayRound() to supply the end date first
        $r = DateRange::eitherWayRound($d1, $d2);
        $this->assertDateRange('2019-04-21 to 2019-04-25', $r);
        $r = DateRange::eitherWayRound($d2, $d1);
        $this->assertDateRange('2019-04-21 to 2019-04-25', $r);
        $r = DateRange::eitherWayRound($d1, $d1);
        $this->assertDateRange('2019-04-21 to 2019-04-21', $r);

        // Create by specifying start and length
        $r = DateRange::fromStartAndInnerLength($d1, 0);
        $this->assertDateRange('2019-04-21 to 2019-04-21', $r);
        $r = DateRange::fromStartAndInnerLength($d1, 3);
        $this->assertDateRange('2019-04-21 to 2019-04-24', $r);
        $this->assertThrows(InvalidArgumentException::class, function () use ($d1) {
            /** @phpstan-ignore argument.type (We are deliberately passing an out of range integer to trigger exception)  */
            $r = DateRange::fromStartAndInnerLength($d1, -1);
        });
        $r = DateRange::fromStartAndOuterLength($d1, 1);
        $this->assertDateRange('2019-04-21 to 2019-04-21', $r);
        $r = DateRange::fromStartAndOuterLength($d1, 4);
        $this->assertDateRange('2019-04-21 to 2019-04-24', $r);
        $this->assertThrows(InvalidArgumentException::class, function () use ($d1) {
            /** @phpstan-ignore argument.type (We are deliberately passing an out of range integer to trigger exception)  */
            $r = DateRange::fromStartAndOuterLength($d1, 0);
        });

        // Create by specifying start/end and duration
        $r = DateRange::fromStartAndDuration($d1, 0, 0, 0);
        $this->assertDateRange('2019-04-21 to 2019-04-21', $r);
        $r = DateRange::fromStartAndDuration($d1, days: 3);
        $this->assertDateRange('2019-04-21 to 2019-04-24', $r);
        $r = DateRange::fromStartAndDuration($d1, months: 3);
        $this->assertDateRange('2019-04-21 to 2019-07-21', $r);
        $r = DateRange::fromStartAndDuration($d1, years: 3);
        $this->assertDateRange('2019-04-21 to 2022-04-21', $r);
        $r = DateRange::fromStartAndDuration($d1, 1, 1, 1);
        $this->assertDateRange('2019-04-21 to 2020-05-22', $r);
        $r = DateRange::fromStartAndDuration($d1, months: 2, days: -6);
        $this->assertDateRange('2019-04-21 to 2019-06-15', $r);
        $r = DateRange::fromEndAndDuration($d1, 0, 0, 0);
        $this->assertDateRange('2019-04-21 to 2019-04-21', $r);
        $r = DateRange::fromEndAndDuration($d1, days: 3);
        $this->assertDateRange('2019-04-18 to 2019-04-21', $r);
        $r = DateRange::fromEndAndDuration($d1, months: 3);
        $this->assertDateRange('2019-01-21 to 2019-04-21', $r);
        $r = DateRange::fromEndAndDuration($d1, years: 3);
        $this->assertDateRange('2016-04-21 to 2019-04-21', $r);
        $r = DateRange::fromEndAndDuration($d1, 1, 1, 1);
        $this->assertDateRange('2018-03-20 to 2019-04-21', $r);
        $r = DateRange::fromEndAndDuration($d1, months: 2, days: -6);
        $this->assertDateRange('2019-02-27 to 2019-04-21', $r);
        $this->assertThrows(InvalidArgumentException::class, function () use ($d1) {
            $r = DateRange::fromStartAndDuration($d1, years: -1);
        });
        $this->assertThrows(InvalidArgumentException::class, function () use ($d1) {
            $r = DateRange::fromEndAndDuration($d1, years: -1);
        });

        // Test creating for currentWeek, currentMonth, currentYear
        $today = JustDate::today();
        $start_of_week = $today->subDays($today->day_of_week->numDaysSince(DayOfWeek::Monday));
        $end_of_week = $start_of_week->addDays(6);
        $r = DateRange::currentWeek();
        $this->assertDateRange("{$start_of_week} to {$end_of_week}", $r);
        foreach (DayOfWeek::cases() as $dow) {
            $offset = (($today->day_of_week->value - $dow->value) + 7) % 7;
            $start_of_week = $today->subDays($offset);
            $end_of_week = $start_of_week->addDays(6);
            $r = DateRange::currentWeek(week_starts_on: $dow);
            $this->assertDateRange("{$start_of_week} to {$end_of_week}", $r);
        }
        $r = DateRange::currentMonth();
        $this->assertDateRange(date('Y-m-01') . ' to ' . date('Y-m-t'), $r);
        $r = DateRange::currentYear();
        $this->assertDateRange(date('Y-01-01') . ' to ' . date('Y-12-31'), $r);
    }

    public function test_range_getters(): void
    {
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');
        $this->assertJustDate('2019-04-21', $r1->start);
        $this->assertJustDate('2019-04-25', $r1->end);
        $this->assertSame(4, $r1->inner_length);
        $this->assertSame(5, $r1->outer_length);

        $r2 = DateRange::fromYmd('2019-04-21', '2019-04-21');
        $this->assertJustDate('2019-04-21', $r2->start);
        $this->assertJustDate('2019-04-21', $r2->end);
        $this->assertSame(0, $r2->inner_length);
        $this->assertSame(1, $r2->outer_length);
    }

    public function test_range_includes(): void
    {
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');
        $this->assertFalse($r1->includes(JustDate::make(2019, 04, 20)));
        $this->assertTrue($r1->includes(JustDate::make(2019, 04, 21)));
        $this->assertTrue($r1->includes(JustDate::make(2019, 04, 23)));
        $this->assertTrue($r1->includes(JustDate::make(2019, 04, 25)));
        $this->assertFalse($r1->includes(JustDate::make(2019, 04, 27)));
    }

    public function test_range_is_same_as(): void
    {
        $r1 = DateRange::fromYmd('2024-08-01', '2024-08-05');
        $this->assertTrue($r1->isSameAs($r1));
        $this->assertTrue($r1->isSameAs(DateRange::fromYmd('2024-08-01', '2024-08-05')));
        $this->assertFalse($r1->isSameAs(DateRange::fromYmd('2024-08-01', '2024-08-04')));
        $this->assertFalse($r1->isSameAs(DateRange::fromYmd('2024-08-02', '2024-08-05')));
        $r2 = DateRange::fromYmd('2024-08-04', '2024-08-04');
        $this->assertTrue($r2->isSameAs($r2));
        $this->assertTrue($r2->isSameAs(DateRange::fromYmd('2024-08-04', '2024-08-04')));
        $this->assertFalse($r2->isSameAs(DateRange::fromYmd('2024-08-12', '2024-08-12')));
    }

    public function test_range_iterators(): void
    {
        // Normal range
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');

        $test_str = '';
        foreach ($r1->each() as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('2122232425', $test_str);

        $test_str = '';
        foreach ($r1->eachExceptLast() as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('21222324', $test_str);

        // Same but in reverse
        $test_str = '';
        foreach ($r1->each(backwards: true) as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('2524232221', $test_str);

        $test_str = '';
        foreach ($r1->eachExceptLast(backwards: true) as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('25242322', $test_str);

        // Single-day range
        $r2 = DateRange::fromYmd('2019-04-21', '2019-04-21');

        $test_str = '';
        foreach ($r2->each() as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('21', $test_str);

        $test_var = false;
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($r2->eachExceptLast() as $date) {
            $test_var = true;
        }
        $this->assertSame(false, $test_var);

        // Same but in reverse
        $test_str = '';
        foreach ($r2->each(backwards: true) as $date) {
            $this->assertInstanceOf(JustDate::class, $date);
            $test_str .= $date->day;
        }
        $this->assertSame('21', $test_str);

        $test_var = false;
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($r2->eachExceptLast(backwards: true) as $date) {
            $test_var = true;
        }
        $this->assertSame(false, $test_var);
    }

    public function test_range_intersection_and_contains(): void
    {
        $r1 = DateRange::fromYmd('2019-04-21', '2019-04-25');

        // Overlap before
        $r2 = DateRange::fromYmd('2019-04-18', '2019-04-23');
        $this->assertDateRange('2019-04-21 to 2019-04-23', DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // Overlap after
        $r2 = DateRange::fromYmd('2019-04-22', '2019-04-28');
        $this->assertDateRange('2019-04-22 to 2019-04-25', DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // Completely surrounding
        $r2 = DateRange::fromYmd('2019-04-22', '2019-04-24');
        $this->assertDateRange('2019-04-22 to 2019-04-24', DateRange::intersection($r1, $r2));
        $this->assertTrue($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // No overlap
        $r2 = DateRange::fromYmd('2019-04-16', '2019-04-18');
        $this->assertNull(DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        $r2 = DateRange::fromYmd('2019-04-26', '2019-04-28');
        $this->assertNull(DateRange::intersection($r1, $r2));
        $this->assertFalse($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        // Endpoints only
        $r2 = DateRange::fromYmd('2019-04-21', '2019-04-21');
        $this->assertDateRange('2019-04-21 to 2019-04-21', DateRange::intersection($r1, $r2));
        $this->assertTrue($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));

        $r2 = DateRange::fromYmd('2019-04-25', '2019-04-25');
        $this->assertDateRange('2019-04-25 to 2019-04-25', DateRange::intersection($r1, $r2));
        $this->assertTrue($r1->contains($r2));
        $this->assertFalse($r2->contains($r1));
    }

    public function test_iterating_sub_ranges(): void
    {
        // Split the range into subranges by month
        $r1 = DateRange::fromYmd('2021-02-28', '2021-04-02');
        $getMonth = function (JustDate $date) {
            return $date->month;
        };
        $subranges = [];
        foreach ($r1->eachSubRange($getMonth) as $subrange) {
            $subranges[] = $subrange;
        }
        $this->assertSame(3, count($subranges));
        $this->assertDateRange('2021-02-28 to 2021-02-28', $subranges[0]['range']);
        $this->assertSame(2, $subranges[0]['value']);
        $this->assertDateRange('2021-03-01 to 2021-03-31', $subranges[1]['range']);
        $this->assertSame(3, $subranges[1]['value']);
        $this->assertDateRange('2021-04-01 to 2021-04-02', $subranges[2]['range']);
        $this->assertSame(4, $subranges[2]['value']);

        // Same but backwards
        $subranges = [];
        foreach ($r1->eachSubRange($getMonth, backwards: true) as $subrange) {
            $subranges[] = $subrange;
        }
        $this->assertSame(3, count($subranges));
        $this->assertDateRange('2021-04-01 to 2021-04-02', $subranges[0]['range']);
        $this->assertSame(4, $subranges[0]['value']);
        $this->assertDateRange('2021-03-01 to 2021-03-31', $subranges[1]['range']);
        $this->assertSame(3, $subranges[1]['value']);
        $this->assertDateRange('2021-02-28 to 2021-02-28', $subranges[2]['range']);
        $this->assertSame(2, $subranges[2]['value']);

        // Single date range
        $r2 = DateRange::fromYmd('2021-02-28', '2021-02-28');
        $subranges = [];
        foreach ($r2->eachSubRange($getMonth) as $subrange) {
            $subranges[] = $subrange;
        }
        $this->assertSame(1, count($subranges));
        $this->assertDateRange('2021-02-28 to 2021-02-28', $subranges[0]['range']);
        $this->assertSame(2, $subranges[0]['value']);
    }

    public function test_serialization(): void
    {
        $d1 = JustDate::make(2019, 4, 21);
        $s = serialize($d1);
        $this->assertTrue(is_string($s));
        $_d1 = unserialize($s);
        $this->assertJustDate('2019-04-21', $_d1);
        $this->assertNotSame($d1, $_d1);

        $d2 = JustDate::make(2019, 4, 25);
        $r = DateRange::make($d1, $d2);
        $s = serialize($r);
        $this->assertTrue(is_string($s));
        $_r = unserialize($s);
        $this->assertDateRange('2019-04-21 to 2019-04-25', $_r);
        $this->assertNotSame($r, $_r);

        $this->assertSame('"2019-04-21"', json_encode($d1));
        $this->assertSame('{"start":"2019-04-21","end":"2019-04-25"}', json_encode($r));
    }

    public function test_add_working_days(): void
    {
        $d1 = JustDate::make(2023, 9, 4); // Monday
        $this->assertJustDate('2023-09-04', $d1->addWorkingDays(0));
        $this->assertJustDate('2023-09-05', $d1->addWorkingDays(1)); // Tue
        $this->assertJustDate('2023-09-08', $d1->addWorkingDays(4)); // Fri
        $this->assertJustDate('2023-09-11', $d1->addWorkingDays(5)); // Next Mon
        $this->assertJustDate('2023-09-04', $d1->addWorkingDays(-1));

        // Make sure we advance to the first working day if we start on one that isn't
        $d2 = JustDate::make(2023, 9, 2); // Saturday
        $this->assertJustDate('2023-09-04', $d2->addWorkingDays(0)); // Mon
        $this->assertJustDate('2023-09-05', $d2->addWorkingDays(1)); // Tue

        $holidays = new DateSet(JustDate::make(2023, 9, 4), JustDate::make(2023, 9, 7)); // Mon and Thur
        $this->assertJustDate('2023-09-05', $d1->addWorkingDays(0, $holidays)); // Tue
        $this->assertJustDate('2023-09-06', $d1->addWorkingDays(1, $holidays)); // Wed
        $this->assertJustDate('2023-09-12', $d1->addWorkingDays(4, $holidays)); // Next Tue
        $this->assertJustDate('2023-09-13', $d1->addWorkingDays(5, $holidays)); // Next Wed
        $this->assertJustDate('2023-09-05', $d1->addWorkingDays(-1, $holidays)); // Tue
    }

    public function test_day_of_week_enum(): void
    {
        $mon = DayOfWeek::Monday;
        $this->assertSame(DayOfWeek::Wednesday, $mon->addDays(2));
        $this->assertSame(DayOfWeek::Tuesday, $mon->addDays(8));
        $this->assertSame(DayOfWeek::Sunday, $mon->subDays(1));

        foreach ([0 => true, 1 => false, 2 => false, 3 => false, 4 => false, 5 => false, 6 => true] as $i => $is_weekend) {
            $dow = DayOfWeek::from($i);
            if ($is_weekend) {
                $this->assertTrue($dow->isWeekend());
                $this->assertFalse($dow->isWeekday());
            } else {
                $this->assertFalse($dow->isWeekend());
                $this->assertTrue($dow->isWeekday());
            }
            $this->assertEquals(0, $dow->numDaysUntil($dow));
            $this->assertEquals(0, $dow->numDaysSince($dow));
        }

        $this->assertEquals(1, DayOfWeek::Sunday->numDaysUntil(DayOfWeek::Monday));
        $this->assertEquals(4, DayOfWeek::Friday->numDaysUntil(DayOfWeek::Tuesday));
        $this->assertEquals(5, DayOfWeek::Monday->numDaysUntil(DayOfWeek::Saturday));
        $this->assertEquals(6, DayOfWeek::Monday->numDaysUntil(DayOfWeek::Sunday));
        $this->assertEquals(6, DayOfWeek::Sunday->numDaysSince(DayOfWeek::Monday));
        $this->assertEquals(3, DayOfWeek::Friday->numDaysSince(DayOfWeek::Tuesday));
        $this->assertEquals(2, DayOfWeek::Monday->numDaysSince(DayOfWeek::Saturday));
        $this->assertEquals(1, DayOfWeek::Monday->numDaysSince(DayOfWeek::Sunday));
    }
}
