<?php

/** @noinspection DuplicatedCode */

use MadisonSolutions\JustDate\BaseDateSet;
use MadisonSolutions\JustDate\DateRange;
use MadisonSolutions\JustDate\DateRangeList;
use MadisonSolutions\JustDate\DateSet;
use MadisonSolutions\JustDate\JustDate;
use MadisonSolutions\JustDate\MutableDateSet;
use PHPUnit\Framework\TestCase;

class BaseDateSetTest extends TestCase
{
    public function test_serialization(): void
    {
        $tests = [
            [
                [],
                '',
                [],
            ],
            [
                [JustDate::fromYmd('2021-04-15')],
                '2021-04-15',
                [['start' => '2021-04-15', 'end' => '2021-04-15']],
            ],
            [
                [DateRange::fromYmd('2021-04-15', '2021-04-20')],
                '2021-04-15 to 2021-04-20',
                [['start' => '2021-04-15', 'end' => '2021-04-20']],
            ],
            [
                [DateRange::fromYmd('2021-02-01', '2021-02-15'), JustDate::fromYmd('2021-04-04'), DateRange::fromYmd('2021-05-20', '2021-05-22')],
                '2021-02-01 to 2021-02-15, 2021-04-04, 2021-05-20 to 2021-05-22',
                [
                    ['start' => '2021-02-01', 'end' => '2021-02-15'],
                    ['start' => '2021-04-04', 'end' => '2021-04-04'],
                    ['start' => '2021-05-20', 'end' => '2021-05-22'],
                ],
            ],
        ];

        foreach ($tests as [$args, $expected_string, $expected_json_obj]) {
            $set = new DateSet(...$args);
            $serialised = serialize($set);
            $restored = unserialize($serialised);
            $this->assertInstanceOf(DateSet::class, $restored);
            $this->assertEquals($expected_string, (string) $set);
            $this->assertEquals($expected_string, (string) $restored);
            $json = json_encode($set);
            $this->assertEquals(json_encode($expected_json_obj), $json);

            $set = new MutableDateSet(...$args);
            $serialised = serialize($set);
            $restored = unserialize($serialised);
            $this->assertInstanceOf(MutableDateSet::class, $restored);
            $this->assertEquals($expected_string, (string) $set);
            $this->assertEquals($expected_string, (string) $restored);
            $json = json_encode($set);
            $this->assertEquals(json_encode($expected_json_obj), $json);
        }
    }

    public function test_generators(): void
    {
        $tests = [
            [
                [],
                [],
                [],
            ],
            [
                [JustDate::fromYmd('2021-04-15')],
                ['2021-04-15'],
                ['2021-04-15 to 2021-04-15'],
            ],
            [
                [DateRange::fromYmd('2021-04-15', '2021-04-17')],
                ['2021-04-15', '2021-04-16', '2021-04-17'],
                ['2021-04-15 to 2021-04-17'],
            ],
            [
                [DateRange::fromYmd('2021-02-01', '2021-02-03'), JustDate::fromYmd('2021-02-08'), DateRange::fromYmd('2021-02-10', '2021-02-11')],
                ['2021-02-01', '2021-02-02', '2021-02-03', '2021-02-08', '2021-02-10', '2021-02-11'],
                ['2021-02-01 to 2021-02-03', '2021-02-08 to 2021-02-08', '2021-02-10 to 2021-02-11'],
            ],
        ];

        foreach ($tests as [$args, $expected_dates, $expected_ranges]) {
            foreach ([new DateSet(...$args), new MutableDateSet(...$args)] as $set) {
                /**
                 * @var BaseDateSet $set
                 */
                $dates = [];
                foreach ($set->eachDate() as $date) {
                    $dates[] = (string) $date;
                }
                $this->assertEquals($expected_dates, $dates);
                $dates = [];
                foreach ($set->eachDate(backwards: true) as $date) {
                    $dates[] = (string) $date;
                }
                $this->assertEquals(array_reverse($expected_dates), $dates);

                $ranges = [];
                foreach ($set->eachRange() as $range) {
                    $ranges[] = (string) $range;
                }
                $this->assertEquals($expected_ranges, $ranges);
                $ranges = [];
                foreach ($set->eachRange(backwards: true) as $range) {
                    $ranges[] = (string) $range;
                }
                $this->assertEquals(array_reverse($expected_ranges), $ranges);
            }
        }
    }

    public function test_is_same_as(): void
    {
        $set = new DateSet(JustDate::fromYmd('2024-08-01'), JustDate::fromYmd('2024-08-02'), JustDate::fromYmd('2024-08-03'));
        $this->assertTrue($set->isSameAs(DateRange::fromYmd('2024-08-01', '2024-08-03')));

        $same = [
            ['', new DateSet],
            ['', new MutableDateSet],
            ['2024-08-04', JustDate::fromYmd('2024-08-04')],
            ['2024-08-04', DateRange::fromYmd('2024-08-04', '2024-08-04')],
            ['2024-08-04 to 2024-08-10', DateRange::fromYmd('2024-08-04', '2024-08-10')],
        ];

        foreach ($same as $test) {
            $this->assertTrue(DateSet::fromString($test[0])->isSameAs($test[1]));
            $this->assertTrue(MutableDateSet::fromString($test[0])->isSameAs($test[1]));
        }

        $same_set = [
            '',
            '2024-08-04',
            '2024-08-04 to 2024-08-10',
            '2024-08-04 to 2024-08-10, 2024-09-01',
            '2023-01-01, 2024-08-04 to 2024-08-10',
            '2023-01-01, 2024-08-04 to 2024-08-10, 2024-09-01',
            '2023-01-01 to 2023-01-31, 2024-01-01 to 2024-01-31',
        ];

        foreach ($same_set as $str) {
            $this->assertTrue(DateSet::fromString($str)->isSameAs(DateSet::fromString($str)));
            $this->assertTrue(DateSet::fromString($str)->isSameAs(MutableDateSet::fromString($str)));
            $this->assertTrue(MutableDateSet::fromString($str)->isSameAs(DateSet::fromString($str)));
            $this->assertTrue(MutableDateSet::fromString($str)->isSameAs(MutableDateSet::fromString($str)));
        }

        /**
         * None of the sets in the first array are the same as any of the sets in the second array
         *
         * @var array{0:list<string>, 1:list<DateRangeList|string>}
         */
        $not_same = [
            ['', '2024-08-01', '2024-08-01 to 2024-08-06', '2024-08-01, 2024-08-02'],
            [
                JustDate::fromYmd('2024-08-04'),
                DateRange::fromYmd('2024-08-04', '2024-08-04'),
                DateRange::fromYmd('2024-08-04', '2024-08-06'),
                '2024-08-04',
                '2024-08-04 to 2024-08-06',
                '2024-08-01, 2024-08-04 to 2024-08-06',
                '2024-08-01 to 2024-08-05',
            ],
        ];

        foreach ($not_same[0] as $str) {
            $set = DateSet::fromString($str);
            $mutable_set = MutableDateSet::fromString($str);
            foreach ($not_same[1] as $other) {
                if (is_string($other)) {
                    $this->assertFalse($set->isSameAs(DateSet::fromString($other)));
                    $this->assertFalse($set->isSameAs(MutableDateSet::fromString($other)));
                    $this->assertFalse($mutable_set->isSameAs(DateSet::fromString($other)));
                    $this->assertFalse($mutable_set->isSameAs(MutableDateSet::fromString($other)));
                } else {
                    $this->assertFalse($set->isSameAs($other));
                    $this->assertFalse($mutable_set->isSameAs($other));
                }
            }
        }
    }

    public function test_contains(): void
    {
        /**
         * @var list<array{0: string, 1:DateRangeList|string, 2:bool}>
         */
        $tests = [
            ['', '', true],
            ['', '2024-01-01', false],
            ['', JustDate::fromYmd('2024-01-01'), false],
            ['2024-08-04', '', true],
            ['2024-08-04 to 2024-08-06', '', true],
            ['2024-08-04', '2024-08-04', true],
            ['2024-08-04', '2024-08-05', false],
            ['2024-08-04', JustDate::fromYmd('2024-08-04'), true],
            ['2024-08-04', DateRange::fromYmd('2024-08-04', '2024-08-04'), true],
            ['2024-08-04, 2024-10-01', '2024-08-04', true],
            ['2024-08-04, 2024-10-01', JustDate::fromYmd('2024-08-05'), false],
            ['2024-08-04, 2024-10-01', JustDate::fromYmd('2024-08-04'), true],
            ['2024-08-04, 2024-10-01', DateRange::fromYmd('2024-08-04', '2024-08-04'), true],
            ['2024-08-01 to 2024-08-20', JustDate::fromYmd('2024-08-01'), true],
            ['2024-08-01 to 2024-08-20', JustDate::fromYmd('2024-08-20'), true],
            ['2024-08-01 to 2024-08-20', DateRange::fromYmd('2024-08-01', '2024-08-05'), true],
            ['2024-08-01 to 2024-08-20', DateRange::fromYmd('2024-08-05', '2024-08-15'), true],
            ['2024-08-01 to 2024-08-20', DateRange::fromYmd('2024-08-15', '2024-08-20'), true],
            ['2024-08-01 to 2024-08-20', JustDate::fromYmd('2024-08-21'), false],
            ['2024-08-01 to 2024-08-20', DateRange::fromYmd('2024-08-15', '2024-08-21'), false],
            ['2024-08-01 to 2024-08-20', '2024-08-02 to 2024-08-07, 2024-08-14 to 2024-08-20', true],
            ['2024-01-01, 2024-08-01 to 2024-08-20', DateRange::fromYmd('2024-08-05', '2024-08-15'), true],
            ['2024-01-01, 2024-08-01 to 2024-08-20', JustDate::fromYmd('2024-01-01'), true],
            ['2024-01-01, 2024-08-01 to 2024-08-20', '2024-01-01, 2024-08-01, 2024-08-05 to 2024-08-10, 2024-08-20', true],
            ['2024-01-01, 2024-08-01 to 2024-08-20', '2024-01-01, 2024-02-01, 2024-08-01, 2024-08-05 to 2024-08-10, 2024-08-20', false],
        ];

        foreach ($tests as $test) {
            $set = DateSet::fromString($test[0]);
            $mutable_set = MutableDateSet::fromString($test[0]);
            $other = $test[1];
            $expected = $test[2];
            if (is_string($other)) {
                $other_set = DateSet::fromString($other);
                $other_mutable_set = MutableDateSet::fromString($other);
                $this->assertSame($expected, $set->contains($other_set));
                $this->assertSame($expected, $set->contains($other_mutable_set));
                $this->assertSame($expected, $mutable_set->contains($other_set));
                $this->assertSame($expected, $mutable_set->contains($other_mutable_set));
            } else {
                $this->assertSame($expected, $set->contains($other));
                $this->assertSame($expected, $mutable_set->contains($other));
            }
        }

        // A set always should contain itself
        foreach (array_unique(array_column($tests, 0)) as $str) {
            $set = DateSet::fromString($str);
            $mutable_set = MutableDateSet::fromString($str);
            $this->assertTrue($set->contains($set));
            $this->assertTrue($set->contains($mutable_set));
            $this->assertTrue($mutable_set->contains($set));
            $this->assertTrue($mutable_set->contains($mutable_set));
        }
    }
}
