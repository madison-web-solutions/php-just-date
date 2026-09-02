<?php

namespace MadisonSolutions\JustDate;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class MonthAndYear
 *
 * Class representing a particular month in a particular year, with no day or time information
 */
class MonthAndYear implements DateRangeList, JsonSerializable
{
    /**
     * Create a new MonthAndYear object from year and month
     *
     * Note that once created, the MonthAndYear is immutable, there's no way to alter the internal values.
     * It is possible to supply a month value which is outside of the normal 1 - 12 range and
     * the year and month will be adjusted to correspond.
     * eg supplying 0 for the $month will result in December of the previous year.
     *
     * @param  int  $year  The Year (full, 4 digit year)
     * @param  int  $month  The month (1 = January ... 12 = December)
     * @return MonthAndYear The new MonthAndYear instance
     */
    public static function make(int $year, int $month): MonthAndYear
    {
        return MonthAndYear::fromMonthIndex(MonthAndYear::monthIndex($year, $month));
    }

    /**
     * Create a new MonthAndYear object from a string in Y-m format
     *
     * @param  string  $ym  The month in Y-m format, eg '2019-04'
     * @return MonthAndYear The new MonthAndYear instance
     *
     * @throws InvalidArgumentException If the string does not contain a valid month in Y-m format
     */
    public static function fromYm(string $ym): MonthAndYear
    {
        return MonthAndYear::make(...MonthAndYear::parseYm($ym));
    }

    /**
     * Get year and month integers from a string in Y-m format, if valid
     *
     * @param  string  $ym  The month in Y-m format, eg '2019-04'
     * @return array{0: int, 1: int} Array containing integers [year, month]
     *
     * @throws InvalidArgumentException If the string does not contain a valid month in Y-m format
     */
    public static function parseYm(string $ym): array
    {
        if (preg_match('/^(\d\d\d\d)-(\d\d)$/', trim($ym), $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            if ($month >= 1 && $month <= 12) {
                return [$year, $month];
            }
        }
        throw new InvalidArgumentException("Invalid Y-m month '{$ym}'");
    }

    /**
     * Create a new MonthAndYear object containing the given date
     *
     * @param  JustDate  $date  The date
     * @return MonthAndYear The new MonthAndYear instance
     */
    public static function fromDate(JustDate $date): MonthAndYear
    {
        return MonthAndYear::make($date->year, $date->month);
    }

    /**
     * Create a new MonthAndYear object from a DateTime object
     *
     * The month and year will be taken from the DateTime object in its own timezone
     *
     * @param  DateTime  $date  The DateTime object (remains unchanged)
     * @return MonthAndYear The new MonthAndYear instance
     */
    public static function fromDateTime(DateTime $date): MonthAndYear
    {
        return MonthAndYear::make((int) $date->format('Y'), (int) $date->format('m'));
    }

    /**
     * Get the current month
     *
     * If a timezone is specified, the month will be whatever the month is right now in the specified timezone
     * If timezone is omitted, the month will be whatever the month is right now in the system default timezone
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone
     * @return MonthAndYear The new MonthAndYear instance
     */
    public static function thisMonth(?DateTimeZone $timezone = null): MonthAndYear
    {
        return MonthAndYear::fromDate(JustDate::today($timezone));
    }

    /**
     * Get the previous month
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone - if specified the month will be one month before whatever the month is right now in the specified timezone
     * @return MonthAndYear The new MonthAndYear instance
     */
    public static function lastMonth(?DateTimeZone $timezone = null): MonthAndYear
    {
        return MonthAndYear::thisMonth($timezone)->addMonths(-1);
    }

    /**
     * Get the next month
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone - if specified the month will be one month after whatever the month is right now in the specified timezone
     * @return MonthAndYear The new MonthAndYear instance
     */
    public static function nextMonth(?DateTimeZone $timezone = null): MonthAndYear
    {
        return MonthAndYear::thisMonth($timezone)->addMonths(1);
    }

    /**
     * Return the (signed) number of months between 2 MonthAndYear objects: $a and $b
     *
     * If $a is before $b the return value will be positive
     * If $a is after $b the return value will be negative
     * If $a and $b refer to the same month, the return value will be zero
     *
     * @param  MonthAndYear  $a  The start month
     * @param  MonthAndYear  $b  The end month
     * @return int The number of months from $a to $b
     */
    public static function difference(MonthAndYear $a, MonthAndYear $b): int
    {
        return $b->month_index - $a->month_index;
    }

    /**
     * Compare 2 MonthAndYear objects for sorting purposes
     *
     * @param  MonthAndYear  $a  The first month
     * @param  MonthAndYear  $b  The second month
     * @return int Negative if $a is before $b, positive if $a is after $b, zero if they are the same month
     */
    public static function compare(MonthAndYear $a, MonthAndYear $b): int
    {
        return $a->month_index <=> $b->month_index;
    }

    /**
     * Return the earliest of a set of months
     *
     * @param  MonthAndYear  $first  The first month
     * @param  MonthAndYear  ...$others  Any number of other months
     * @return MonthAndYear The earliest month from the set
     */
    public static function earliest(MonthAndYear $first, MonthAndYear ...$others): MonthAndYear
    {
        $earliest = $first;
        foreach ($others as $month) {
            if ($month->isBefore($earliest)) {
                $earliest = $month;
            }
        }
        return $earliest;
    }

    /**
     * Return the latest of a set of months
     *
     * @param  MonthAndYear  $first  The first month
     * @param  MonthAndYear  ...$others  Any number of other months
     * @return MonthAndYear The latest month from the set
     */
    public static function latest(MonthAndYear $first, MonthAndYear ...$others): MonthAndYear
    {
        $latest = $first;
        foreach ($others as $month) {
            if ($month->isAfter($latest)) {
                $latest = $month;
            }
        }
        return $latest;
    }

    /**
     * Convert a year and month to a single integer counting months from January of year 0
     */
    protected static function monthIndex(int $year, int $month): int
    {
        return ($year * 12) + ($month - 1);
    }

    /**
     * Create a new MonthAndYear from a month index (as produced by monthIndex())
     */
    protected static function fromMonthIndex(int $month_index): MonthAndYear
    {
        $year = intdiv($month_index, 12);
        $month = $month_index % 12;
        if ($month < 0) {
            $month += 12;
            $year -= 1;
        }
        return new MonthAndYear($year, $month + 1);
    }

    /**
     * The year as an integer
     */
    public readonly int $year;

    /**
     * The month as an integer (1 = January ... 12 = December)
     */
    public readonly int $month;

    /**
     * Integer used internally for comparisons and arithmetic
     *
     * @internal
     */
    protected int $month_index;

    /**
     * DateTime object created and used internally when required for formatting
     *
     * @internal
     */
    protected ?DateTime $_date = null;

    /**
     * MonthAndYear constructor.
     *
     * Expects $month to already be in the range 1 - 12
     */
    protected function __construct(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
        $this->month_index = MonthAndYear::monthIndex($year, $month);
        $this->_date = null;
    }

    /**
     * Get the internal DateTime object for 00:00 on the first day of this month (UTC)
     * Creates the DateTime object if it doesn't already exist
     */
    protected function getInternalDateTime(): DateTime
    {
        static $utc = null;
        if (! $this->_date) {
            if (is_null($utc)) {
                $utc = new DateTimeZone('UTC');
            }
            $this->_date = new DateTime;
            $this->_date->setTimezone($utc);
            $this->_date->setDate($this->year, $this->month, 1);
            $this->_date->setTime(0, 0, 0, 0);
        }
        return $this->_date;
    }

    /**
     * Standard string representation is Y-m format
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Create a string representation of the month, with the given format
     *
     * Note that any day or time values which are requested in the format will be those of 00:00:00 on the first day of the month
     *
     * @param  string  $format  The format, as per PHP's date() function
     * @return string The formatted string
     */
    public function format(string $format = 'Y-m'): string
    {
        return $this->getInternalDateTime()->format($format);
    }

    /**
     * Get the first date of this month
     *
     * @return JustDate The new JustDate object
     */
    public function firstDate(): JustDate
    {
        return JustDate::make($this->year, $this->month, 1);
    }

    /**
     * Get the last date of this month
     *
     * @return JustDate The new JustDate object
     */
    public function lastDate(): JustDate
    {
        return JustDate::make($this->year, $this->month + 1, 0);
    }

    /**
     * Get the range of dates spanning this month
     *
     * @return DateRange The new DateRange object
     */
    public function range(): DateRange
    {
        return DateRange::make($this->firstDate(), $this->lastDate());
    }

    /**
     * Get the number of days in this month
     *
     * @return int The number of days
     */
    public function numDays(): int
    {
        return $this->range()->outer_length;
    }

    /**
     * Add the specified number of months and return the new MonthAndYear object
     *
     * @param  int  $months  The number of months to add (can be negative)
     * @return MonthAndYear The new MonthAndYear object
     */
    public function addMonths(int $months): MonthAndYear
    {
        return MonthAndYear::fromMonthIndex($this->month_index + $months);
    }

    /**
     * Subtract the specified number of months and return the new MonthAndYear object
     *
     * @param  int  $months  The number of months to subtract (can be negative)
     * @return MonthAndYear The new MonthAndYear object
     */
    public function subMonths(int $months): MonthAndYear
    {
        return $this->addMonths(-$months);
    }

    /**
     * Add the specified number of years and return the new MonthAndYear object
     *
     * @param  int  $years  The number of years to add (can be negative)
     * @return MonthAndYear The new MonthAndYear object
     */
    public function addYears(int $years): MonthAndYear
    {
        return $this->addMonths($years * 12);
    }

    /**
     * Subtract the specified number of years and return the new MonthAndYear object
     *
     * @param  int  $years  The number of years to subtract (can be negative)
     * @return MonthAndYear The new MonthAndYear object
     */
    public function subYears(int $years): MonthAndYear
    {
        return $this->addMonths(-$years * 12);
    }

    /**
     * Get the month after this one
     *
     * @return MonthAndYear The new MonthAndYear object
     */
    public function next(): MonthAndYear
    {
        return $this->addMonths(1);
    }

    /**
     * Get the month before this one
     *
     * @return MonthAndYear The new MonthAndYear object
     */
    public function prev(): MonthAndYear
    {
        return $this->addMonths(-1);
    }

    /**
     * Test whether the given date falls within this month
     *
     * @param  JustDate  $date  The date to test
     * @return bool True if the date is in this month, false otherwise
     */
    public function includes(JustDate $date): bool
    {
        return $date->year == $this->year && $date->month == $this->month;
    }

    /**
     * Test whether this month is the same as another
     *
     * @param  MonthAndYear  $other  The other month to compare to
     * @return bool True if the months are the same
     */
    public function isSameAs(MonthAndYear $other): bool
    {
        return $this->month_index == $other->month_index;
    }

    /**
     * Test whether this month is before another
     *
     * @param  MonthAndYear  $other  The other month to compare to
     * @return bool True if this month is before the other
     */
    public function isBefore(MonthAndYear $other): bool
    {
        return $this->month_index < $other->month_index;
    }

    /**
     * Test whether this month is before or the same as another
     *
     * @param  MonthAndYear  $other  The other month to compare to
     * @return bool True if this month is before or the same as the other
     */
    public function isBeforeOrSameAs(MonthAndYear $other): bool
    {
        return $this->month_index <= $other->month_index;
    }

    /**
     * Test whether this month is after another
     *
     * @param  MonthAndYear  $other  The other month to compare to
     * @return bool True if this month is after the other
     */
    public function isAfter(MonthAndYear $other): bool
    {
        return $this->month_index > $other->month_index;
    }

    /**
     * Test whether this month is after or the same as another
     *
     * @param  MonthAndYear  $other  The other month to compare to
     * @return bool True if this month is after or the same as the other
     */
    public function isAfterOrSameAs(MonthAndYear $other): bool
    {
        return $this->month_index >= $other->month_index;
    }

    /**
     * Serialize
     *
     * The year and month integers completely define a MonthAndYear object, so they are sufficient for serialization
     *
     * @internal
     *
     * @return array{year: int, month: int}
     */
    public function __serialize(): array
    {
        return ['year' => $this->year, 'month' => $this->month];
    }

    /**
     * Unserialize
     *
     * @internal
     *
     * @param  array{year: int, month: int}  $data
     */
    public function __unserialize(array $data)
    {
        $this->__construct((int) $data['year'], (int) $data['month']);
    }

    /**
     * Json serialize to the Y-m string
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    /**
     * @internal
     *
     * @return DateRange[]
     */
    public function getRanges(): array
    {
        return [$this->range()];
    }
}
