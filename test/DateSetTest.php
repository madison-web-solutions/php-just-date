<?php

/** @noinspection DuplicatedCode */

use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\DateSet;
use MadisonSolutions\JustDate\JustDate;
use PHPUnit\Framework\TestCase;

class DateSetTest extends TestCase
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
            $set = new DateSet(...$args);
            $this->assertEquals($expected, (string) $set);
            $set = DateSet::union(...$args);
            $this->assertEquals($expected, (string) $set);
            $set = DateSet::fromString($expected);
            $this->assertEquals($expected, (string) $set);
        }

        // Test creating using only dates
        $set = DateSet::fromDates(JustDate::fromYmd('2021-04-11'), JustDate::fromYmd('2021-04-10'));
        $this->assertEquals('2021-04-10 to 2021-04-11', (string) $set);
    }

    public function test_adding_ranges(): void
    {
        $set = new DateSet;
        $this->assertEquals('', (string) $set);

        // Add a range
        $set = DateSet::union($set, DateRange::fromYmd('2021-04-01', '2021-04-05'));
        $this->assertEquals('2021-04-01 to 2021-04-05', (string) $set);

        // Add another before
        $set = DateSet::union($set, DateRange::fromYmd('2021-03-01', '2021-03-05'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-05', (string) $set);

        // Add another after
        $set = DateSet::union($set, DateRange::fromYmd('2021-05-01', '2021-05-05'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-05, 2021-05-01 to 2021-05-05', (string) $set);

        // Add another in between, not touching
        $set = DateSet::union($set, DateRange::fromYmd('2021-04-20', '2021-04-25'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-05, 2021-04-20 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Add some ranges overlapping existing ranges
        $set = DateSet::union($set, DateRange::fromYmd('2021-04-03', '2021-04-10'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-10, 2021-04-20 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);
        $set = DateSet::union($set, DateRange::fromYmd('2021-04-18', '2021-04-22'));
        $this->assertEquals('2021-03-01 to 2021-03-05, 2021-04-01 to 2021-04-10, 2021-04-18 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);
        $set = DateSet::union($set, DateRange::fromYmd('2021-02-20', '2021-03-20'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-10, 2021-04-18 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Add some ranges touching existing ranges
        $set = DateSet::union($set, DateRange::fromYmd('2021-04-11', '2021-04-12'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-12, 2021-04-18 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);
        $set = DateSet::union($set, DateRange::fromYmd('2021-04-15', '2021-04-17'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-12, 2021-04-15 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Plug a gap
        $set = DateSet::union($set, DateRange::fromYmd('2021-04-13', '2021-04-14'));
        $this->assertEquals('2021-02-20 to 2021-03-20, 2021-04-01 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set);

        // Add some range that overlap multiple existing ranges
        $set = DateSet::union($set, DateRange::fromYmd('2021-01-10', '2021-04-29'));
        $this->assertEquals('2021-01-10 to 2021-04-29, 2021-05-01 to 2021-05-05', (string) $set);
        $set = DateSet::union($set, DateRange::fromYmd('2021-01-01', '2021-05-20'));
        $this->assertEquals('2021-01-01 to 2021-05-20', (string) $set);
    }

    public function test_adding_dates(): void
    {
        $set = new DateSet;
        $this->assertEquals('', (string) $set);

        // Add a date
        $set = DateSet::union($set, JustDate::fromYmd('2021-04-01'));
        $this->assertEquals('2021-04-01', (string) $set);

        // Add some more dates
        $set = DateSet::union($set, JustDate::fromYmd('2021-04-05'));
        $this->assertEquals('2021-04-01, 2021-04-05', (string) $set);
        $set = DateSet::union($set, JustDate::fromYmd('2021-04-04'));
        $this->assertEquals('2021-04-01, 2021-04-04 to 2021-04-05', (string) $set);

        // Add an existing date again
        $set = DateSet::union($set, JustDate::fromYmd('2021-04-04'));
        $this->assertEquals('2021-04-01, 2021-04-04 to 2021-04-05', (string) $set);

        // Plug the gap
        $set = DateSet::union($set, JustDate::fromYmd('2021-04-02'));
        $set = DateSet::union($set, JustDate::fromYmd('2021-04-03'));
        $this->assertEquals('2021-04-01 to 2021-04-05', (string) $set);
    }

    public function test_adding_sets(): void
    {
        $set1 = new DateSet(DateRange::fromYmd('2021-04-01', '2021-04-05'), DateRange::fromYmd('2021-04-20', '2021-04-25'));

        $set2 = DateSet::union($set1, new DateSet(DateRange::fromYmd('2021-04-11', '2021-04-12'), DateRange::fromYmd('2021-04-15', '2021-04-19')));
        $this->assertEquals('2021-04-01 to 2021-04-05, 2021-04-11 to 2021-04-12, 2021-04-15 to 2021-04-25', (string) $set2);
        $this->assertEquals('2021-04-01 to 2021-04-05, 2021-04-20 to 2021-04-25', (string) $set1);

        $set3 = DateSet::union($set2, new DateSet(DateRange::fromYmd('2021-03-01', '2021-03-10'), DateRange::fromYmd('2021-05-01', '2021-05-05')));
        $this->assertEquals('2021-03-01 to 2021-03-10, 2021-04-01 to 2021-04-05, 2021-04-11 to 2021-04-12, 2021-04-15 to 2021-04-25, 2021-05-01 to 2021-05-05', (string) $set3);

        $set4 = DateSet::union($set3, new DateSet(JustDate::fromYmd('2021-05-06'), DateRange::fromYmd('2021-03-08', '2021-05-01')));
        $this->assertEquals('2021-03-01 to 2021-05-06', (string) $set4);
    }

    public function test_subtracting(): void
    {
        $set = new DateSet(DateRange::fromYmd('2021-02-01', '2021-05-30'));

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
        $set0 = new DateSet;
        $set1 = new DateSet(DateRange::fromYmd('2021-02-01', '2021-05-30'));
        $set2 = new DateSet(JustDate::fromYmd('2021-04-10'));
        $set3 = new DateSet(DateRange::fromYmd('2021-07-01', '2021-07-20'));

        // Intersection with empty set is also empty
        $this->assertEquals('', (string) DateSet::intersection($set0, $set0));
        $this->assertEquals('', (string) DateSet::intersection($set0, $set1));
        $this->assertEquals('', (string) DateSet::intersection($set0, $set2));

        // Intersection with self set is self
        $this->assertEquals('', (string) DateSet::intersection($set0, $set0));
        $this->assertEquals('2021-02-01 to 2021-05-30', (string) DateSet::intersection($set1, $set1));
        $this->assertEquals('2021-04-10', (string) DateSet::intersection($set2, $set2));

        // Single day intersection
        $this->assertEquals('2021-04-10', (string) DateSet::intersection($set1, $set2));

        // Non-overlapping intersection
        $this->assertEquals('', (string) DateSet::intersection($set1, $set3));

        // Try some more complex intersections

        $set = DateSet::intersection(
            DateRange::fromYmd('2021-03-01', '2021-04-30'),
            new DateSet(DateRange::fromYmd('2021-02-10', '2021-02-15'), DateRange::fromYmd('2021-02-25', '2021-03-10'), DateRange::fromYmd('2021-04-20', '2021-05-10'))
        );
        $this->assertEquals('2021-03-01 to 2021-03-10, 2021-04-20 to 2021-04-30', (string) $set);

        $set = DateSet::intersection(
            DateRange::fromYmd('2021-03-01', '2021-04-30'),
            new DateSet(DateRange::fromYmd('2021-02-10', '2021-02-15'), DateRange::fromYmd('2021-02-25', '2021-03-10'), DateRange::fromYmd('2021-04-20', '2021-05-10')),
            new DateSet(JustDate::fromYmd('2021-02-20'), JustDate::fromYmd('2021-03-01'), JustDate::fromYmd('2021-03-02'), JustDate::fromYmd('2021-04-20'))
        );
        $this->assertEquals('2021-03-01 to 2021-03-02, 2021-04-20', (string) $set);
    }

    public function test_includes(): void
    {
        $set = new DateSet(
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

    public function test_is_empty(): void
    {
        $set = new DateSet;
        $this->assertTrue($set->isEmpty());

        $set = new DateSet(JustDate::fromYmd('2021-04-10'));
        $this->assertFalse($set->isEmpty());

        $set = new DateSet(DateRange::fromYmd('2021-04-04', '2021-04-10'));
        $this->assertFalse($set->isEmpty());

        $set = $set->subtract(DateRange::fromYmd('2021-04-04', '2021-04-10'));
        $this->assertTrue($set->isEmpty());
    }

    public function test_spanning_range(): void
    {
        $set = new DateSet;
        $this->assertNull($set->getSpanningRange());

        $set = new DateSet(JustDate::fromYmd('2021-04-10'));
        $this->assertEquals('2021-04-10 to 2021-04-10', (string) $set->getSpanningRange());

        $set = new DateSet(JustDate::fromYmd('2021-04-10'), JustDate::fromYmd('2021-05-10'));
        $this->assertEquals('2021-04-10 to 2021-05-10', (string) $set->getSpanningRange());
    }

    public function test_window_generator(): void
    {
        $tests = [
            [
                // Empty set, all dates in window false
                new DateSet,
                DateRange::fromYmd('2021-04-01', '2021-04-05'),
                ['2021-04-01' => false, '2021-04-02' => false, '2021-04-03' => false, '2021-04-04' => false, '2021-04-05' => false],
            ],
            [
                // Set contains a single date in the window
                new DateSet(JustDate::fromYmd('2021-04-03')),
                DateRange::fromYmd('2021-04-01', '2021-04-05'),
                ['2021-04-01' => false, '2021-04-02' => false, '2021-04-03' => true, '2021-04-04' => false, '2021-04-05' => false],
            ],
            [
                // Set contains some dates before the window
                new DateSet(DateRange::fromYmd('2021-03-01', '2021-03-10')),
                DateRange::fromYmd('2021-04-01', '2021-04-05'),
                ['2021-04-01' => false, '2021-04-02' => false, '2021-04-03' => false, '2021-04-04' => false, '2021-04-05' => false],
            ],
            [
                // Set contains some dates after the window
                new DateSet(DateRange::fromYmd('2021-06-01', '2021-06-10')),
                DateRange::fromYmd('2021-04-01', '2021-04-05'),
                ['2021-04-01' => false, '2021-04-02' => false, '2021-04-03' => false, '2021-04-04' => false, '2021-04-05' => false],
            ],
            [
                // Set contains some dates in, and outside the window
                new DateSet(
                    JustDate::fromYmd('2021-03-01'),
                    DateRange::fromYmd('2021-03-20', '2021-04-02'),
                    JustDate::fromYmd('2021-04-04'),
                    DateRange::fromYmd('2021-04-06', '2021-06-10')
                ),
                DateRange::fromYmd('2021-04-01', '2021-04-06'),
                ['2021-04-01' => true, '2021-04-02' => true, '2021-04-03' => false, '2021-04-04' => true, '2021-04-05' => false, '2021-04-06' => true],
            ],
            [
                // Set covers the window completely
                new DateSet(DateRange::fromYmd('2021-04-01', '2021-04-30')),
                DateRange::fromYmd('2021-04-01', '2021-04-05'),
                ['2021-04-01' => true, '2021-04-02' => true, '2021-04-03' => true, '2021-04-04' => true, '2021-04-05' => true],
            ],
        ];

        foreach ($tests as [$set, $window, $expected]) {
            /**
             * @var DateSet $set
             * @var DateRange $window
             */
            $actual = [];
            foreach ($set->window($window) as [$date, $in_set]) {
                $actual[(string) $date] = $in_set;
            }
            $this->assertEquals($expected, $actual);
        }
    }
}
