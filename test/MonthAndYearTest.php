<?php

/** @noinspection DuplicatedCode */

use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\DateSet;
use MadisonSolutions\JustDate\JustDate;
use MadisonSolutions\JustDate\MonthAndYear;
use MadisonSolutions\JustDate\MutableDateSet;
use PHPUnit\Framework\TestCase;

class MonthAndYearTest extends TestCase
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

    protected function assertMonthAndYear(string $expectedYm, mixed $actual): void
    {
        $this->assertInstanceOf(MonthAndYear::class, $actual);
        $this->assertSame($expectedYm, (string) $actual);
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

    public function test_create_month_and_years(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $this->assertMonthAndYear('2026-09', $m);

        $m = MonthAndYear::make(2026, 1);
        $this->assertMonthAndYear('2026-01', $m);

        $m = MonthAndYear::make(2026, 12);
        $this->assertMonthAndYear('2026-12', $m);
    }

    public function test_out_of_range_months_are_adjusted(): void
    {
        $this->assertMonthAndYear('2027-01', MonthAndYear::make(2026, 13));
        $this->assertMonthAndYear('2027-12', MonthAndYear::make(2026, 24));
        $this->assertMonthAndYear('2028-01', MonthAndYear::make(2026, 25));
        $this->assertMonthAndYear('2025-12', MonthAndYear::make(2026, 0));
        $this->assertMonthAndYear('2025-01', MonthAndYear::make(2026, -11));
        $this->assertMonthAndYear('2024-12', MonthAndYear::make(2026, -12));
        $this->assertMonthAndYear('2024-11', MonthAndYear::make(2026, -13));
    }

    public function test_create_from_ym(): void
    {
        $this->assertMonthAndYear('2026-09', MonthAndYear::fromYm('2026-09'));
        $this->assertMonthAndYear('2026-09', MonthAndYear::fromYm(' 2026-09 '));
        $this->assertMonthAndYear('2026-01', MonthAndYear::fromYm('2026-01'));
        $this->assertMonthAndYear('2026-12', MonthAndYear::fromYm('2026-12'));
    }

    public function test_cannot_create_from_invalid_ym(): void
    {
        foreach (['foo', '', '2026', '2026-9', '26-09', '2026-13', '2026-00', '2026-09-01', '2026/09'] as $invalid) {
            $this->assertThrows(InvalidArgumentException::class, function () use ($invalid) {
                MonthAndYear::fromYm($invalid);
            });
        }
    }

    public function test_create_from_dates(): void
    {
        $this->assertMonthAndYear('2026-09', MonthAndYear::fromDate(JustDate::fromYmd('2026-09-15')));
        $this->assertMonthAndYear('2026-09', MonthAndYear::fromDate(JustDate::fromYmd('2026-09-01')));
        $this->assertMonthAndYear('2026-09', MonthAndYear::fromDate(JustDate::fromYmd('2026-09-30')));
        $this->assertMonthAndYear('2026-09', JustDate::fromYmd('2026-09-15')->monthAndYear());

        $dt = new DateTime('2026-09-15 23:30:00', new DateTimeZone('UTC'));
        $this->assertMonthAndYear('2026-09', MonthAndYear::fromDateTime($dt));

        // Month is taken in the DateTime's own timezone
        $dt = new DateTime('2026-09-30 23:30:00', new DateTimeZone('America/New_York'));
        $this->assertMonthAndYear('2026-09', MonthAndYear::fromDateTime($dt));
        $dt->setTimezone(new DateTimeZone('UTC'));
        $this->assertMonthAndYear('2026-10', MonthAndYear::fromDateTime($dt));
    }

    public function test_this_month(): void
    {
        $today = JustDate::today();
        $this->assertMonthAndYear($today->format('Y-m'), MonthAndYear::thisMonth());
        $this->assertMonthAndYear($today->addMonths(-1)->format('Y-m'), MonthAndYear::lastMonth());
        $this->assertMonthAndYear($today->addMonths(1)->format('Y-m'), MonthAndYear::nextMonth());

        $tz = new DateTimeZone('Pacific/Kiritimati');
        $this->assertMonthAndYear(JustDate::today($tz)->format('Y-m'), MonthAndYear::thisMonth($tz));
    }

    public function test_getters(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $this->assertSame(2026, $m->year);
        $this->assertSame(9, $m->month);

        $m = MonthAndYear::make(2026, 13);
        $this->assertSame(2027, $m->year);
        $this->assertSame(1, $m->month);
    }

    public function test_first_and_last_dates(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $this->assertJustDate('2026-09-01', $m->firstDate());
        $this->assertJustDate('2026-09-30', $m->lastDate());
        $this->assertSame(30, $m->numDays());

        $m = MonthAndYear::make(2026, 12);
        $this->assertJustDate('2026-12-01', $m->firstDate());
        $this->assertJustDate('2026-12-31', $m->lastDate());
        $this->assertSame(31, $m->numDays());

        $m = MonthAndYear::make(2026, 2);
        $this->assertJustDate('2026-02-01', $m->firstDate());
        $this->assertJustDate('2026-02-28', $m->lastDate());
        $this->assertSame(28, $m->numDays());

        $m = MonthAndYear::make(2028, 2);
        $this->assertJustDate('2028-02-01', $m->firstDate());
        $this->assertJustDate('2028-02-29', $m->lastDate());
        $this->assertSame(29, $m->numDays());

        $m = MonthAndYear::make(2100, 2);
        $this->assertJustDate('2100-02-28', $m->lastDate());
    }

    public function test_range(): void
    {
        $this->assertDateRange('2026-09-01 to 2026-09-30', MonthAndYear::make(2026, 9)->range());
        $this->assertDateRange('2028-02-01 to 2028-02-29', MonthAndYear::make(2028, 2)->range());
    }

    public function test_traversing_the_calendar(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $this->assertMonthAndYear('2026-10', $m->addMonths(1));
        $this->assertMonthAndYear('2027-01', $m->addMonths(4));
        $this->assertMonthAndYear('2029-09', $m->addMonths(36));
        $this->assertMonthAndYear('2026-08', $m->addMonths(-1));
        $this->assertMonthAndYear('2025-12', $m->addMonths(-9));
        $this->assertMonthAndYear('2026-09', $m->addMonths(0));

        $this->assertMonthAndYear('2026-08', $m->subMonths(1));
        $this->assertMonthAndYear('2025-12', $m->subMonths(9));
        $this->assertMonthAndYear('2026-10', $m->subMonths(-1));

        $this->assertMonthAndYear('2027-09', $m->addYears(1));
        $this->assertMonthAndYear('2016-09', $m->addYears(-10));
        $this->assertMonthAndYear('2025-09', $m->subYears(1));
        $this->assertMonthAndYear('2036-09', $m->subYears(-10));

        $this->assertMonthAndYear('2026-10', $m->next());
        $this->assertMonthAndYear('2026-08', $m->prev());
        $this->assertMonthAndYear('2027-01', MonthAndYear::make(2026, 12)->next());
        $this->assertMonthAndYear('2025-12', MonthAndYear::make(2026, 1)->prev());

        // Original is unchanged
        $this->assertMonthAndYear('2026-09', $m);
    }

    public function test_difference_and_compare(): void
    {
        $a = MonthAndYear::make(2026, 9);
        $b = MonthAndYear::make(2027, 3);
        $this->assertSame(6, MonthAndYear::difference($a, $b));
        $this->assertSame(-6, MonthAndYear::difference($b, $a));
        $this->assertSame(0, MonthAndYear::difference($a, $a));
        $this->assertSame(0, MonthAndYear::difference($a, MonthAndYear::make(2026, 9)));

        $this->assertSame(-1, MonthAndYear::compare($a, $b));
        $this->assertSame(1, MonthAndYear::compare($b, $a));
        $this->assertSame(0, MonthAndYear::compare($a, MonthAndYear::make(2026, 9)));

        $months = [$b, MonthAndYear::make(2020, 12), $a];
        usort($months, [MonthAndYear::class, 'compare']);
        $this->assertSame(['2020-12', '2026-09', '2027-03'], array_map('strval', $months));
    }

    public function test_earliest_and_latest(): void
    {
        $a = MonthAndYear::make(2026, 9);
        $b = MonthAndYear::make(2027, 3);
        $c = MonthAndYear::make(2020, 12);

        $this->assertMonthAndYear('2026-09', MonthAndYear::earliest($a));
        $this->assertMonthAndYear('2020-12', MonthAndYear::earliest($a, $b, $c));
        $this->assertMonthAndYear('2020-12', MonthAndYear::earliest($c, $b, $a));
        $this->assertMonthAndYear('2026-09', MonthAndYear::latest($a));
        $this->assertMonthAndYear('2027-03', MonthAndYear::latest($a, $b, $c));
        $this->assertMonthAndYear('2027-03', MonthAndYear::latest($b, $c, $a));
    }

    public function test_comparisons(): void
    {
        $a = MonthAndYear::make(2026, 9);
        $b = MonthAndYear::make(2026, 10);
        $a2 = MonthAndYear::make(2025, 21);

        $this->assertTrue($a->isSameAs($a2));
        $this->assertFalse($a->isSameAs($b));

        $this->assertTrue($a->isBefore($b));
        $this->assertFalse($b->isBefore($a));
        $this->assertFalse($a->isBefore($a2));

        $this->assertTrue($a->isBeforeOrSameAs($b));
        $this->assertTrue($a->isBeforeOrSameAs($a2));
        $this->assertFalse($b->isBeforeOrSameAs($a));

        $this->assertTrue($b->isAfter($a));
        $this->assertFalse($a->isAfter($b));
        $this->assertFalse($a->isAfter($a2));

        $this->assertTrue($b->isAfterOrSameAs($a));
        $this->assertTrue($a->isAfterOrSameAs($a2));
        $this->assertFalse($a->isAfterOrSameAs($b));
    }

    public function test_includes(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $this->assertTrue($m->includes(JustDate::fromYmd('2026-09-01')));
        $this->assertTrue($m->includes(JustDate::fromYmd('2026-09-15')));
        $this->assertTrue($m->includes(JustDate::fromYmd('2026-09-30')));
        $this->assertFalse($m->includes(JustDate::fromYmd('2026-08-31')));
        $this->assertFalse($m->includes(JustDate::fromYmd('2026-10-01')));
        $this->assertFalse($m->includes(JustDate::fromYmd('2025-09-15')));
    }

    public function test_formatting(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $this->assertSame('2026-09', (string) $m);
        $this->assertSame('2026-09', $m->format());
        $this->assertSame('September 2026', $m->format('F Y'));
        $this->assertSame('Sep 26', $m->format('M y'));
        $this->assertSame('9/2026', $m->format('n/Y'));
        // Day and time values come from 00:00:00 on the first of the month
        $this->assertSame('September 01', $m->format('F d'));
        $this->assertSame('2026-09-01 00:00:00', $m->format('Y-m-d H:i:s'));
        $this->assertSame('Tuesday', $m->format('l'));
    }

    public function test_use_as_date_range_list(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $this->assertCount(1, $m->getRanges());
        $this->assertDateRange('2026-09-01 to 2026-09-30', $m->getRanges()[0]);

        $set = new DateSet($m);
        $this->assertSame('2026-09-01 to 2026-09-30', (string) $set);
        $this->assertTrue($set->contains($m));
        $this->assertTrue($set->isSameAs($m));

        $set = new DateSet($m, JustDate::fromYmd('2026-10-05'));
        $this->assertSame('2026-09-01 to 2026-09-30, 2026-10-05', (string) $set);
        $this->assertTrue($set->contains($m));
        $this->assertFalse($set->contains($m->next()));

        $set = DateSet::union($m, $m->next());
        $this->assertSame('2026-09-01 to 2026-10-31', (string) $set);

        $set = DateSet::intersection($m, DateRange::fromYmd('2026-09-20', '2026-10-10'));
        $this->assertSame('2026-09-20 to 2026-09-30', (string) $set);

        $set = $set->subtract($m);
        $this->assertSame('', (string) $set);

        $set = new MutableDateSet;
        $set->add($m);
        $set->remove(JustDate::fromYmd('2026-09-15'));
        $this->assertSame('2026-09-01 to 2026-09-14, 2026-09-16 to 2026-09-30', (string) $set);
    }

    public function test_serialization(): void
    {
        $m = MonthAndYear::make(2026, 9);
        $s = serialize($m);
        $this->assertTrue(is_string($s));
        $_m = unserialize($s);
        $this->assertMonthAndYear('2026-09', $_m);
        $this->assertNotSame($m, $_m);
        $this->assertSame(2026, $_m->year);
        $this->assertSame(9, $_m->month);
        $this->assertSame('September 2026', $_m->format('F Y'));
        $this->assertTrue($m->isSameAs($_m));
        $this->assertMonthAndYear('2026-10', $_m->addMonths(1));

        $this->assertSame('"2026-09"', json_encode($m));
        $this->assertSame('{"m":"2026-09"}', json_encode(['m' => $m]));
    }
}
