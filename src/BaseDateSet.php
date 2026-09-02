<?php

namespace MadisonSolutions\JustDate;

use Generator;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class BaseDateSet
 *
 * Base class for DateSet and MutableDateSet
 */
abstract class BaseDateSet implements DateRangeList, JsonSerializable
{
    /**
     * @var list<DateRange>
     */
    protected $ranges;

    /**
     * Utility function that takes any number of JustDate objects and turns them into a normalized list of DateRanges
     *
     * Any consecutive dates in the input will be merged into a single range
     * Any repeated dates will be merged together
     * The resulting list of ranges will be sorted and disjoint
     *
     * @param  JustDate  ...$dates  The input dates
     * @return list<DateRange> Resulting normalized list of ranges
     */
    public static function sortedRangesFromSingleDates(JustDate ...$dates): array
    {
        $ranges = [];
        $num = count($dates);
        if ($num == 0) {
            return $ranges;
        }
        usort($dates, fn ($a, $b) => $a->epoch_day - $b->epoch_day);
        $curr_start = $curr_end = $dates[0];
        for ($i = 1; $i < $num; $i++) {
            if ($dates[$i]->isAfter($curr_end->nextDay())) {
                // there's a gap before the next date
                $ranges[] = DateRange::make($curr_start, $curr_end);
                $curr_start = $curr_end = $dates[$i];
            } else {
                $curr_end = $dates[$i];
            }
        }
        $ranges[] = DateRange::make($curr_start, $curr_end);
        return $ranges;
    }

    /**
     * Utility function for subtracting a range from an array of ranges
     *
     * Used internally by the DateSet and MutableDateSet classes
     * The supplied array must already be normalized (which is why this function is protected)
     *
     * @param  DateRange[]  $ranges  Original sorted, disjoint list of ranges
     * @param  DateRange  $cut  Range to be subtracted from each of the ranges
     * @return list<DateRange> Resulting normalized list of ranges after subtracting $cut
     */
    protected static function subtractRangeFromSortedRanges(array $ranges, DateRange $cut): array
    {
        $num = count($ranges);
        if ($num == 0) {
            return [];
        }

        $i = 0;
        $new_ranges = [];

        // Start looping over the existing ranges
        // Any which are completely before $cut will go into the new set unchanged
        while ($i < $num && $cut->start->isAfter($ranges[$i]->end)) {
            $new_ranges[] = $ranges[$i];
            $i++;
        }

        // We've now gone past all the ranges that were completely before $cut
        // Continue looping, but now considering ranges that are intersecting $cut in some way
        while ($i < $num && $cut->end->isAfterOrSameAs($ranges[$i]->start)) {
            $existing = $ranges[$i];
            if ($cut->start->isBeforeOrSameAs($existing->start)) {
                if ($cut->end->isAfterOrSameAs($existing->end)) {
                    // $cut completely covers $existing, so $existing is removed completely
                } else {
                    // some of the beginning of $existing is removed by $cut
                    $new_ranges[] = DateRange::make($cut->end->nextDay(), $existing->end);
                }
            } elseif ($cut->end->isAfterOrSameAs($existing->end)) {
                // some of the end of $existing is removed by $cut
                $new_ranges[] = DateRange::make($existing->start, $cut->start->prevDay());
            } else {
                // only remaining possibility is that $cut is completely contained within $existing
                // so it must split $existing into 2 disjoint ranges
                $new_ranges[] = DateRange::make($existing->start, $cut->start->prevDay());
                $new_ranges[] = DateRange::make($cut->end->nextDay(), $existing->end);
            }
            $i++;
        }

        // Any remaining ranges must be completely after $cut, so are unchanged
        while ($i < $num) {
            $new_ranges[] = $ranges[$i];
            $i++;
        }

        return $new_ranges;
    }

    /**
     * Utility function for determining the ranges that are in the intersection of 2 date sets
     *
     * Used internally by DateSet and MutableDateSet objects
     * The returned array of ranges is normalized (sorted and disjoint)
     *
     * @return list<DateRange> Normalised list of ranges in the intersection of $a and $b
     */
    protected static function getIntersectingRanges(BaseDateSet $a, BaseDateSet $b): array
    {
        $intersection = [];
        foreach ($a->ranges as $range_a) {
            foreach ($b->ranges as $range_b) {
                $intersection[] = DateRange::intersection($range_a, $range_b);
            }
        }
        return BaseDateSet::normalizeRanges(array_filter($intersection));
    }

    /**
     * Utility function to 'normalize' a list of ranges
     *
     * This means merging together any touching or overlapping ranges, and sorting into date order.
     * The resulting array of ranges are guaranteed to be sorted and disjoint.
     * Used internally by DateSet and MutableDate set objects.
     *
     * @param  DateRange[]  $in
     * @return list<DateRange>
     */
    protected static function normalizeRanges(array $in): array
    {
        $num = count($in);
        if ($num == 0) {
            return [];
        }

        usort($in, fn ($a, $b) => $a->start->epoch_day - $b->start->epoch_day);

        $out = [];
        $curr = $in[0];
        for ($i = 1; $i < $num; $i++) {
            $next = $in[$i];
            if ($next->start->isAfter($curr->end->nextDay())) {
                // there's a gap between $curr and $next
                $out[] = $curr;
                $curr = $next;
            } elseif ($next->end->isAfter($curr->end)) {
                // $next overlaps or touches the end of $curr, so extend $curr
                $curr = DateRange::make($curr->start, $next->end);
            } else {
                // $next is completely contained within $curr
            }
        }
        $out[] = $curr;

        return $out;
    }

    abstract public function __construct(DateRangeList ...$lists);

    /**
     * Determine whether the given date is a member of this set
     */
    public function includes(JustDate $date): bool
    {
        foreach ($this->ranges as $range) {
            if ($range->end->isAfterOrSameAs($date)) {
                if ($range->start->isBeforeOrSameAs($date)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Create a new set by subtracting a Date or DateRange or set of dates from this set
     *
     * The dates in the resulting object will be those that are contained in this set but are not contained
     * in the supplied object. Returns a new set (does not mutate $this)
     */
    public function subtract(DateRangeList $list_to_cut): static
    {
        $instance = new static;
        $ranges = $this->ranges;
        foreach ($list_to_cut->getRanges() as $range_to_cut) {
            $ranges = $this->subtractRangeFromSortedRanges($ranges, $range_to_cut);
        }
        $instance->ranges = $ranges;
        return $instance;
    }

    /**
     * Determine whether this set is empty
     *
     * @return bool True if this set is empty (IE contains no dates), false otherwise
     */
    public function isEmpty(): bool
    {
        return count($this->ranges) == 0;
    }

    /**
     * Fetch the single date range that spans this set
     *
     * Fetch the single date range that covers all the dates in this set
     * IE the returned range will start with the earliest date in this set, and finish with the latest
     * Returns null in case this set is empty
     *
     * @return ?DateRange The spanning DateRange, or null if this set is empty
     */
    public function getSpanningRange(): ?DateRange
    {
        $num = count($this->ranges);
        if ($num == 0) {
            return null;
        }
        $start = $this->ranges[0]->start;
        $end = $this->ranges[$num - 1]->end;
        return DateRange::make($start, $end);
    }

    /**
     * Get a generator which yields each range in the set as a DateRange object
     *
     * @param  bool  $backwards  If true the ranges will be returned in reverse order (default false).
     * @return Generator<int, DateRange>
     */
    public function eachRange(bool $backwards = false): Generator
    {
        $ranges = $backwards ? array_reverse($this->ranges) : $this->ranges;
        foreach ($ranges as $range) {
            yield $range;
        }
    }

    /**
     * Get a generator which yields each date in the set as a JustDate object
     *
     * @param  bool  $backwards  If true the dates will be returned in reverse order (default false).
     * @return Generator<int, JustDate>
     */
    public function eachDate(bool $backwards = false): Generator
    {
        foreach ($this->eachRange($backwards) as $range) {
            yield from $range->each($backwards);
        }
    }

    /**
     * Get a generator which yields whether or not each date in the window range belongs to this set
     *
     * Specifically, the generator will yield an array for each each date in the window range in order
     * The first element of the array will be the JustDate object for that date
     * The second, a boolean, true if the date belongs to this set, and false otherwise.
     *
     * @return Generator<int, array{0: JustDate, 1: bool}>
     */
    public function window(DateRange $window): Generator
    {
        // The idea here is that we'll step over the dates in the window, and keep $curr pointing to
        // either the range that the date is in, or the next range that's coming up.
        // If $curr is false, it will mean that there are no more ranges in the set.
        // $started_curr will be a boolean for tracking whether we've got to $curr or if it's still in the future

        // Work on a copy of the ranges array
        // This is so that nothing else can modify the internal array pointer between iterations
        $ranges = $this->ranges;

        // Start by looking at the first range
        reset($ranges);
        $curr = current($ranges);

        // Skip over any ranges that are completely before the start of the window
        $window_start = $window->start;
        while ($curr && $window_start->isAfter($curr->end)) {
            $curr = next($ranges);
        }

        // See whether at the start of the window, we're already in $curr or not
        $started_curr = ($curr && $window_start->isAfterOrSameAs($curr->start));

        foreach ($window->each() as $date) {
            if ($curr) {
                if (!$started_curr && $date->isSameAs($curr->start)) {
                    // We've now entered $curr
                    $started_curr = true;
                }
                yield [$date, $started_curr];
                if ($started_curr && $date->isSameAs($curr->end)) {
                    // This was the last day of $curr, so we need to move to the next range
                    $started_curr = false;
                    $curr = next($ranges);
                }
            } else {
                yield [$date, false];
            }
        }
    }

    /**
     * Get the string representation of this set
     */
    public function __toString(): string
    {
        return implode(', ', array_map(function (DateRange $range) {
            return (string) ($range->start->isSameAs($range->end) ? $range->start : $range);
        }, $this->ranges));
    }

    /**
     * Get the normalized list of ranges as a plain PHP array
     *
     * @return list<DateRange>
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    /**
     * Json representation is array of ranges
     *
     * @return list<array{start: string, end: string}>
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($range) {
            return $range->jsonSerialize();
        }, $this->ranges);
    }

    /**
     * Unserialize by parsing the standard string representation
     */
    public static function fromString(string $serialized): static
    {
        $parts = explode(',', $serialized);
        $args = array_map(function ($part) {
            $part = trim($part);
            if (empty($part)) {
                return null;
            }
            $sub_parts = explode(' to ', $part);
            switch (count($sub_parts)) {
                case 1:
                    return JustDate::fromYmd(trim($part));
                case 2:
                    return DateRange::fromYmd(trim($sub_parts[0]), trim($sub_parts[1]));
                default:
                    throw new InvalidArgumentException("Invalid date range string '{$part}'");
            }
        }, $parts);
        return new static (...array_filter($args));
    }

    /**
     * Test whether the given object consists of the exact same set of dates as this one
     *
     * @param  DateRangeList  $other  An object implementing DateRangeList to compare with (JustDate, DateRange, DateSet or MutableDateSet)
     * @return bool True if the set of dates in $other is exactly the same as the set of dates in this set, false otherwise
     */
    public function isSameAs(DateRangeList $other): bool
    {
        if ($other instanceof JustDate || $other instanceof DateRange || $other instanceof DateSet || $other instanceof MutableDateSet) {
            // We know in theses cases the ranges are already normalised
            $normalised_other_ranges = $other->getRanges();
        } else {
            // For other classes, we will need to make sure they are normalised before comparing
            $normalised_other_ranges = BaseDateSet::normalizeRanges($other->getRanges());
        }
        foreach ($this->ranges as $i => $range) {
            $other_range = $normalised_other_ranges[$i] ?? null;
            unset($normalised_other_ranges[$i]);
            if ($other_range instanceof DateRange) {
                if (! $range->start->isSameAs($other_range->start)) {
                    return false;
                }
                if (! $range->end->isSameAs($other_range->end)) {
                    return false;
                }
            } else {
                return false;
            }
        }
        if (! empty($normalised_other_ranges)) {
            // $other includes additional dates not in $this
            return false;
        }
        return true;
    }

    /**
     * Test whether this set contains all of the dates in the given DateRange
     *
     * @param  DateRange  $other  DateRange to compare with
     * @return bool True if this set contains all of the dates in $other, false otherwise
     */
    protected function containsRange(DateRange $other): bool
    {
        foreach ($this->ranges as $range) {
            if ($range->contains($other)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Test whether this set contains all of the dates in the given object
     *
     * Note: returns true if $other is an empty DateSet or MutableDateSet.
     *
     * @param  DateRangeList  $other  An object implementing DateRangeList to compare with (JustDate, DateRange, DateSet or MutableDateSet)
     * @return bool True if this set contains all of the dates in $other, false otherwise
     */
    public function contains(DateRangeList $other): bool
    {
        foreach ($other->getRanges() as $other_range) {
            if (! $this->containsRange($other_range)) {
                return false;
            }
        }
        return true;
    }
}
