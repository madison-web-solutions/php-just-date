<?php

/** @noinspection DuplicatedCode */

use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\JustDate;
use MadisonSolutions\JustDate\MutableDateSet;
use PHPUnit\Framework\TestCase;

class MutableDateSetTest extends TestCase
{
    public function test_constructing(): void
    {
        $params = [
            [
                '',
            ],
            [
                JustDate::fromYmd('2021-04-10'),
                '2021-04-10',
            ],
            [
                JustDate::fromYmd('2021-04-10'),
                JustDate::fromYmd('2021-04-11'),
                '2021-04-10 to 2021-04-11',
            ],
            [
                JustDate::fromYmd('2021-04-10'),
                JustDate::fromYmd('2021-04-12'),
                '2021-04-10, 2021-04-12',
            ],
            [
                DateRange::fromYmd('2021-04-10', '2021-04-14'),
                '2021-04-10 to 2021-04-14',
            ],
            [
                DateRange::fromYmd('2021-04-10', '2021-04-14'),
                DateRange::fromYmd('2021-04-10', '2021-04-14'),
                '2021-04-10 to 2021-04-14',
            ],
            [
                DateRange::fromYmd('2021-04-10', '2021-04-14'),
                DateRange::fromYmd('2021-04-15', '2021-04-20'),
                '2021-04-10 to 2021-04-20',
            ],
            [
                DateRange::fromYmd('2021-04-10', '2021-04-14'),
                JustDate::fromYmd('2021-04-16'),
                '2021-04-10 to 2021-04-14, 2021-04-16',
            ],
        ];
        foreach ($params as $args) {
            $expected = array_pop($args);
            $set = new MutableDateSet(...$args);
            $this->assertEquals($expected, (string) $set);
            $set = MutableDateSet::union(...$args);
            $this->assertEquals($expected, (string) $set);
            $set = MutableDateSet::fromString($expected);
            $this->assertEquals($expected, (string) $set);
        }

        // Test creating using only dates
        $set = MutableDateSet::fromDates(JustDate::fromYmd('2021-04-11'), JustDate::fromYmd('2021-04-10'));
        $this->assertEquals('2021-04-10 to 2021-04-11', (string) $set);
    }

    public function test_adding_ranges(): void
    {
        $set = new MutableDateSet;
        $this->assertEquals('', (string) $set);

        // Add a range
        $set->add(DateRange::fromYmd('2021-04-01', '2021-04-05'));
        $this->assertEquals('2021-04-01 to 2021-04-05', (string) $set);

        // Add another before
        $set->add(DateRange::fromYmd('2021-03-01', '2021-03-05'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-05', (string) $set);

        // Add another after
        $set->add(DateRange::fromYmd('2021-05-01', '2021-05-05'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-05, 2021-05-01 to 2021-05-05', (string) $set);

        // Add another in between, not touching
        $set->add(DateRange::fromYmd('2021-04-20', '2021-04-25'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-05, 2021-04-20 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Add some ranges overlapping existing ranges
        $set->add(DateRange::fromYmd('2021-04-03', '2021-04-10'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-10, 2021-04-20 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);
        $set->add(DateRange::fromYmd('2021-04-18', '2021-04-22'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-10, 2021-04-18 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);
        $set->add(DateRange::fromYmd('2021-02-20', '2021-03-20'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-10, 2021-04-18 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Add some ranges touching existing ranges
        $set->add(DateRange::fromYmd('2021-04-11', '2021-04-12'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-12, 2021-04-18 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);
        $set->add(DateRange::fromYmd('2021-04-15', '2021-04-17'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-12, 2021-04-15 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Plug a gap
        $set->add(DateRange::fromYmd('2021-04-13', '2021-04-14'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Add some range that overlap multiple existing ranges
        $set->add(DateRange::fromYmd('2021-01-10', '2021-04-29'));
        $this->assertEquals('2021-01-10 to 2021-04-29, 2021-05-01 to 2021-05-05', (string) $set);
        $set->add(DateRange::fromYmd('2021-01-01', '2021-05-20'));
        $this->assertEquals('2021-01-01 to 2021-05-20', (string) $set);
    }

    public function test_adding_dates(): void
    {
        $set = new MutableDateSet;
        $this->assertEquals('', (string) $set);

        // Add a date
        $set->add(JustDate::fromYmd('2021-04-01'));
        $this->assertEquals('2021-04-01', (string) $set);

        // Add some more dates
        $set->add(JustDate::fromYmd('2021-04-05'));
        $this->assertEquals('2021-04-01, 2021-04-05', (string) $set);
        $set->add(JustDate::fromYmd('2021-04-04'));
        $this->assertEquals('2021-04-01, 2021-04-04 to 2021-04-05', (string) $set);

        // Add an existing date again
        $set->add(JustDate::fromYmd('2021-04-04'));
        $this->assertEquals('2021-04-01, 2021-04-04 to 2021-04-05', (string) $set);

        // Plug the gap
        $set->add(JustDate::fromYmd('2021-04-02'));
        $set->add(JustDate::fromYmd('2021-04-03'));
        $this->assertEquals('2021-04-01 to 2021-04-05', (string) $set);
    }

    public function test_adding_sets(): void
    {
        $set = new MutableDateSet;

        // Add $set1 to $set (should result in both $set and $set1 having the dates from $set1)
        $set1 = new MutableDateSet(DateRange::fromYmd('2021-04-01', '2021-04-05'), DateRange::fromYmd('2021-04-20', '2021-04-25'));
        $set->add($set1);
        $this->assertEquals('2021-04-01 to 2021-04-05, 2021-04-20 to 2021-04-25', (string) $set);
        $this->assertEquals('2021-04-01 to 2021-04-05, 2021-04-20 to 2021-04-25', (string) $set1);

        // Add $set2 to $set ($set should have the new dates, but $set1 should be left unchanged)
        $set2 = new MutableDateSet(DateRange::fromYmd('2021-04-11', '2021-04-12'), DateRange::fromYmd('2021-04-15', '2021-04-19'));
        $set->add($set2);
        $this->assertEquals('2021-04-01 to 2021-04-05, 2021-04-11 to 2021-04-12, 2021-04-15 to 2021-04-25', (string) $set);
        $this->assertEquals('2021-04-01 to 2021-04-05, 2021-04-20 to 2021-04-25', (string) $set1);

        // Add $set3 to $set
        $set3 = new MutableDateSet(DateRange::fromYmd('2021-03-01', '2021-03-10'), DateRange::fromYmd('2021-05-01', '2021-05-05'));
        $set->add($set3);
        $this->assertEquals('2021-03-01 to 2021-03-10, 2021-04-01 to 2021-04-05, 2021-04-11 to 2021-04-12, 2021-04-15 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        $set4 = new MutableDateSet(JustDate::fromYmd('2021-05-06'), DateRange::fromYmd('2021-03-08', '2021-05-01'));
        $set->add($set4);
        $this->assertEquals('2021-03-01 to 2021-05-06', (string) $set);
    }

    public function test_removing(): void
    {
        $set = new MutableDateSet(DateRange::fromYmd('2021-02-01', '2021-05-30'));

        // Remove a day from the middle
        $set->remove(JustDate::fromYmd('2021-03-10'));
        $this->assertEquals('2021-02-01 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);

        // Remove from before the set - no change
        $set->remove(DateRange::fromYmd('2021-01-15', '2021-01-20'));
        $this->assertEquals('2021-02-01 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);

        // Remove from after the set - no change
        $set->remove(DateRange::fromYmd('2021-01-15', '2021-01-20'));
        $this->assertEquals('2021-02-01 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);

        // Try cutting bits off the start of a range
        $set->remove(DateRange::fromYmd('2021-01-20', '2021-02-03'));
        $this->assertEquals('2021-02-04 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);
        $set->remove(DateRange::fromYmd('2021-02-04', '2021-02-05'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);
        $set->remove(DateRange::fromYmd('2021-03-11', '2021-03-11'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-12 to 2021-05-30', (string) $set);

        // Try cutting bits off the end of a range
        $set->remove(DateRange::fromYmd('2021-05-20', '2021-06-01'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-12 to 2021-05-19', (string) $set);
        $set->remove(DateRange::fromYmd('2021-05-19', '2021-05-19'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-12 to 2021-05-18', (string) $set);
        $set->remove(DateRange::fromYmd('2021-03-05', '2021-03-09'));
        $this->assertEquals('2021-02-06 to 2021-03-04, 2021-03-12 to 2021-05-18', (string) $set);

        // Remove from inside a gap - no change
        $set->remove(DateRange::fromYmd('2021-03-05', '2021-03-11'));
        $this->assertEquals('2021-02-06 to 2021-03-04, 2021-03-12 to 2021-05-18', (string) $set);

        // Remove from 2 ranges at once
        $set->remove(DateRange::fromYmd('2021-03-04', '2021-03-12'));
        $this->assertEquals('2021-02-06 to 2021-03-03, 2021-03-13 to 2021-05-18', (string) $set);

        // Remove a larger gap inside a range
        $set->remove(DateRange::fromYmd('2021-04-10', '2021-04-20'));
        $this->assertEquals('2021-02-06 to 2021-03-03, 2021-03-13 to 2021-04-09, 2021-04-21 to 2021-05-18', (string) $set);

        // Remove an entire range
        $set->remove(DateRange::fromYmd('2021-03-13', '2021-04-09'));
        $this->assertEquals('2021-02-06 to 2021-03-03, 2021-04-21 to 2021-05-18', (string) $set);

        // Remove several entire ranges
        $set->remove(DateRange::fromYmd('2021-01-01', '2021-06-01'));
        $this->assertEquals('', (string) $set);
    }

    public function test_subtracting(): void
    {
        $set = new MutableDateSet(DateRange::fromYmd('2021-02-01', '2021-05-30'));

        // Subtract a day from the middle
        $set = $set->subtract(JustDate::fromYmd('2021-03-10'));
        $this->assertEquals('2021-02-01 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);

        // Subtract from before the set - no change
        $set = $set->subtract(DateRange::fromYmd('2021-01-15', '2021-01-20'));
        $this->assertEquals('2021-02-01 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);

        // Subtract from after the set - no change
        $set = $set->subtract(DateRange::fromYmd('2021-01-15', '2021-01-20'));
        $this->assertEquals('2021-02-01 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);

        // Try cutting bits off the start of a range
        $set = $set->subtract(DateRange::fromYmd('2021-01-20', '2021-02-03'));
        $this->assertEquals('2021-02-04 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);
        $set = $set->subtract(DateRange::fromYmd('2021-02-04', '2021-02-05'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-11 to 2021-05-30', (string) $set);
        $set = $set->subtract(DateRange::fromYmd('2021-03-11', '2021-03-11'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-12 to 2021-05-30', (string) $set);

        // Try cutting bits off the end of a range
        $set = $set->subtract(DateRange::fromYmd('2021-05-20', '2021-06-01'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-12 to 2021-05-19', (string) $set);
        $set = $set->subtract(DateRange::fromYmd('2021-05-19', '2021-05-19'));
        $this->assertEquals('2021-02-06 to 2021-03-09, 2021-03-12 to 2021-05-18', (string) $set);
        $set = $set->subtract(DateRange::fromYmd('2021-03-05', '2021-03-09'));
        $this->assertEquals('2021-02-06 to 2021-03-04, 2021-03-12 to 2021-05-18', (string) $set);

        // Subtract from inside a gap - no change
        $set = $set->subtract(DateRange::fromYmd('2021-03-05', '2021-03-11'));
        $this->assertEquals('2021-02-06 to 2021-03-04, 2021-03-12 to 2021-05-18', (string) $set);

        // Subtract from 2 ranges at once
        $set = $set->subtract(DateRange::fromYmd('2021-03-04', '2021-03-12'));
        $this->assertEquals('2021-02-06 to 2021-03-03, 2021-03-13 to 2021-05-18', (string) $set);

        // Subtract a larger gap inside a range
        $set = $set->subtract(DateRange::fromYmd('2021-04-10', '2021-04-20'));
        $this->assertEquals('2021-02-06 to 2021-03-03, 2021-03-13 to 2021-04-09, 2021-04-21 to 2021-05-18', (string) $set);

        // Subtract an entire range
        $set = $set->subtract(DateRange::fromYmd('2021-03-13', '2021-04-09'));
        $this->assertEquals('2021-02-06 to 2021-03-03, 2021-04-21 to 2021-05-18', (string) $set);

        // Subtract several entire ranges
        $set = $set->subtract(DateRange::fromYmd('2021-01-01', '2021-06-01'));
        $this->assertEquals('', (string) $set);
    }

    public function test_intersections(): void
    {
        $set0 = new MutableDateSet;
        $set1 = new MutableDateSet(DateRange::fromYmd('2021-02-01', '2021-05-30'));
        $set2 = new MutableDateSet(JustDate::fromYmd('2021-04-10'));
        $set3 = new MutableDateSet(DateRange::fromYmd('2021-07-01', '2021-07-20'));

        // Intersection with empty set is also empty
        $this->assertEquals('', (string) MutableDateSet::intersection($set0, $set0));
        $this->assertEquals('', (string) MutableDateSet::intersection($set0, $set1));
        $this->assertEquals('', (string) MutableDateSet::intersection($set0, $set2));

        // Intersection with self set is self
        $this->assertEquals('', (string) MutableDateSet::intersection($set0, $set0));
        $this->assertEquals('2021-02-01 to 2021-05-30', (string) MutableDateSet::intersection($set1, $set1));
        $this->assertEquals('2021-04-10', (string) MutableDateSet::intersection($set2, $set2));

        // Single day intersection
        $this->assertEquals('2021-04-10', (string) MutableDateSet::intersection($set1, $set2));

        // Non-overlapping intersection
        $this->assertEquals('', (string) MutableDateSet::intersection($set1, $set3));

        // Try some more complex intersections

        $set = MutableDateSet::intersection(
            DateRange::fromYmd('2021-03-01', '2021-04-30'),
            new MutableDateSet(DateRange::fromYmd('2021-02-10', '2021-02-15'), DateRange::fromYmd('2021-02-25', '2021-03-10'), DateRange::fromYmd('2021-04-20', '2021-05-10'))
        );
        $this->assertEquals('2021-03-01 to 2021-03-10, 2021-04-20 to 2021-04-30', (string) $set);

        $set = MutableDateSet::intersection(
            DateRange::fromYmd('2021-03-01', '2021-04-30'),
            new MutableDateSet(DateRange::fromYmd('2021-02-10', '2021-02-15'), DateRange::fromYmd('2021-02-25', '2021-03-10'), DateRange::fromYmd('2021-04-20', '2021-05-10')),
            new MutableDateSet(JustDate::fromYmd('2021-02-20'), JustDate::fromYmd('2021-03-01'), JustDate::fromYmd('2021-03-02'), JustDate::fromYmd('2021-04-20'))
        );
        $this->assertEquals('2021-03-01 to 2021-03-02, 2021-04-20', (string) $set);
    }

    public function test_includes(): void
    {
        $set = new MutableDateSet(
            DateRange::fromYmd('2021-04-10', '2021-04-20'),
            JustDate::fromYmd('2021-05-01'),
            DateRange::fromYmd('2021-06-01', '2021-06-30')
        );

        $in = [
            '2021-04-10',
            '2021-04-15',
            '2021-04-20',
            '2021-05-01',
            '2021-06-05',
        ];
        foreach ($in as $ymd) {
            $this->assertTrue($set->includes(JustDate::fromYmd($ymd)));
        }

        $out = [
            '2021-04-09',
            '2021-04-25',
            '2021-04-30',
            '2021-07-01',
        ];
        foreach ($out as $ymd) {
            $this->assertFalse($set->includes(JustDate::fromYmd($ymd)));
        }
    }
}
