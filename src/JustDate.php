<?php

/** @noinspection PhpRedundantOptionalArgumentInspection */

namespace MadisonSolutions\JustDate;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class JustDate
 *
 * Class representing a single date with no time information
 *
 * @property-read int $year The year as an integer
 * @property-read int $month The month as an integer (1 = January ... 12 = December)
 * @property-read int $day The day of the month as an integer
 * @property-read DayOfWeek $day_of_week The day of the week
 * @property-read int $timestamp Unix timestamp corresponding to 00:00:00 on this date in UTC
 */
class JustDate implements DateRangeList, JsonSerializable
{
    const SECS_PER_DAY = 86400;

    /**
     * Utility function to create a new DateTime object for the current time, with UTC timezone
     */
    protected static function newUtcDateTime(): DateTime
    {
        static $utc = null;
        if (is_null($utc)) {
            $utc = new DateTimeZone('UTC');
        }
        $date = new DateTime;
        $date->setTimezone($utc);
        return $date;
    }

    /**
     * Create a new JustDate object from year, month and day
     *
     * Note that once created, the JustDate is immutable, there's no way to alter the internal date.
     * It is possible to supply numerical values which are outside of the normal ranges and
     * the internal date value will be adjusted to correspond.
     * eg supplying 0 for the $day will result in the last day of the previous month.
     *
     * @param  int  $year  The Year (full, 4 digit year)
     * @param  int  $month  The month (1 = January ... 12 = December)
     * @param  int  $day  The day of the month (first day is 1)
     * @return JustDate The new JustDate instance
     */
    public static function make(int $year, int $month, int $day): JustDate
    {
        $date = self::newUtcDateTime()
            ->setDate($year, $month, $day)
            ->setTime(0, 0, 0, 0);

        // This is certain to be an integer
        // because the unix timestamp is *defined* to have exactly 86400 seconds per day
        // even if in real life the day contained a leap second
        $epoch_day = $date->getTimestamp() / JustDate::SECS_PER_DAY;
        assert(is_int($epoch_day));

        $instance = new JustDate($epoch_day);
        $instance->_date = $date;
        return $instance;
    }

    /**
     * Create a new JustDate object from the epoch day
     *
     * @param  int  $epoch_day  The number of days since the Unix epoch
     * @return JustDate The new JustDate instance
     */
    public static function fromEpochDay(int $epoch_day): JustDate
    {
        return new JustDate($epoch_day);
    }

    /**
     * Create a new JustDate object from a DateTime object
     *
     * @param  DateTime  $date  The DateTime object (remains unchanged)
     * @return JustDate The new JustDate instance
     */
    public static function fromDateTime(DateTime $date): JustDate
    {
        return JustDate::make((int) $date->format('Y'), (int) $date->format('m'), (int) $date->format('d'));
    }

    /**
     * Get the date at the specified timestamp
     *
     * If a timezone is specified, the date will be whatever the date is in the specified timezone at the specified timestamp
     * If timezone is omitted, the date will be whatever the date is in the system default timezone at the specified timestamp
     *
     * @param  int  $timestamp  The timestamp
     * @param  ?DateTimeZone  $timezone  Optional timezone
     * @return JustDate The new JustDate instance
     */
    public static function fromTimestamp(int $timestamp, ?DateTimeZone $timezone = null): JustDate
    {
        $dt = new DateTime;
        $dt->setTimestamp($timestamp);
        if ($timezone) {
            $dt->setTimezone($timezone);
        }
        return JustDate::fromDateTime($dt);
    }

    /**
     * Get the date that it is today
     *
     * If a timezone is specified, the date will be whatever the date is right now in the specified timezone
     * If timezone is omitted, the date will be whatever the date is right now in the system default timezone
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone
     * @return JustDate The new JustDate instance
     */
    public static function today(?DateTimeZone $timezone = null): JustDate
    {
        $dt = new DateTime;
        if ($timezone) {
            $dt->setTimezone($timezone);
        }
        return JustDate::fromDateTime($dt);
    }

    /**
     * Get the date that it was yesterday
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone - if specified the date will one day before whatever the date is right now in the specified timezone
     * @return JustDate The new JustDate instance
     */
    public static function yesterday(?DateTimeZone $timezone = null): JustDate
    {
        return JustDate::today($timezone)->addDays(-1);
    }

    /**
     * Get the date that it will be tomorrow
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone - if specified the date will one day after whatever the date is right now in the specified timezone
     * @return JustDate The new JustDate instance
     */
    public static function tomorrow(?DateTimeZone $timezone = null): JustDate
    {
        return JustDate::today($timezone)->addDays(1);
    }

    /**
     * Create a new JustDate object from a string in Y-m-d format
     *
     * @param  string  $ymd  The date in Y-m-d format, eg '2019-04-21'
     * @return JustDate The new JustDate instance
     *
     * @throws InvalidArgumentException If the string does not contain a valid date in Y-m-d format
     */
    public static function fromYmd(string $ymd): JustDate
    {
        return JustDate::make(...JustDate::parseYmd($ymd));
    }

    /**
     * Get year month and day integers from a string in Y-m-d format, if valid
     *
     * @param  string  $ymd  The date in Y-m-d format, eg '2019-04-21'
     * @return array{0: int, 1: int, 2: int} Array containing integers [year, month, day]
     *
     * @throws InvalidArgumentException If the string does not contain a valid date in Y-m-d format
     */
    public static function parseYmd(string $ymd): array
    {
        if (preg_match('/^(\d\d\d\d)-(\d\d)-(\d\d)$/', trim($ymd), $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            if (checkdate($month, $day, $year)) {
                return [$year, $month, $day];
            }
        }
        throw new InvalidArgumentException("Invalid Y-m-d date '{$ymd}'");
    }

    /**
     * Return the (signed) number of days between 2 JustDate objects: $a and $b
     *
     * If $a is before $b the return value will be positive
     * If $a is after $b the return value will be negative
     * If $a and $b refer to the same date, the return value will be zero
     *
     * @param  JustDate  $a  The start date
     * @param  JustDate  $b  The end date
     * @return int The number of days from $from to $to
     */
    public static function difference(JustDate $a, JustDate $b): int
    {
        return $b->epoch_day - $a->epoch_day;
    }

    /**
     * Compare 2 JustDate objects and return an integer to indicate which one is earlier
     *
     * Returns -1, 0 or 1, depending on whether $a is respectively earlier than, the same as, or later than $b
     * Can be used as the comparison function in PHP sorting functions, for example usort()
     *
     * If $a is earlier than $b, returns -1
     * If $a and $b refer to the same date, returns zero
     * If $a is later than $b, returns 1
     *
     * @param  JustDate  $a  The first date
     * @param  JustDate  $b  The second date
     * @return int Result of comparison: -1, 0 or 1
     */
    public static function compare(JustDate $a, JustDate $b): int
    {
        return $a->epoch_day <=> $b->epoch_day;
    }

    /**
     * Return the earliest of a set of dates
     *
     * @return JustDate The earliest date from $first and $others
     */
    public static function earliest(JustDate $first, JustDate ...$others): JustDate
    {
        $earliest = $first;
        foreach ($others as $date) {
            if ($date->isBefore($earliest)) {
                $earliest = $date;
            }
        }
        return $earliest;
    }

    /**
     * Return the latest of a set of dates
     *
     * @return JustDate The latest date from $first and $others
     */
    public static function latest(JustDate $first, JustDate ...$others): JustDate
    {
        $latest = $first;
        foreach ($others as $date) {
            if ($date->isAfter($latest)) {
                $latest = $date;
            }
        }
        return $latest;
    }

    /**
     * The number of days since the Unix epoch
     */
    public readonly int $epoch_day;

    /**
     * DateTime object created when required
     * Will be a DateTime object for 00:00 on this date in UTC timezone
     *
     * @internal
     */
    protected ?DateTime $_date;

    /**
     * DayOfWeek object created when required
     *
     * @internal
     */
    protected ?DayOfWeek $_dow;

    /**
     * JustDate constructor.
     */
    protected function __construct(int $epoch_day)
    {
        // $this->epoch_day uniquely determines this date and must be set
        $this->epoch_day = $epoch_day;

        $this->_date = null;
        $this->_dow = null;
    }

    /**
     * Get the internal DateTime object for 00:00 on this date (UTC)
     * Creates the DateTime object if it doesn't already exists
     */
    protected function getInternalDateTime(): DateTime
    {
        if (! $this->_date) {
            $this->_date = self::newUtcDateTime()->setTimestamp($this->timestamp);
        }
        return $this->_date;
    }

    /**
     * @internal
     */
    public function __get(mixed $name): mixed
    {
        switch ($name) {
            case 'year':
                return (int) $this->getInternalDateTime()->format('Y');
            case 'month':
                return (int) $this->getInternalDateTime()->format('m');
            case 'day':
                return (int) $this->getInternalDateTime()->format('d');
            case 'day_of_week':
                if (is_null($this->_dow)) {
                    $this->_dow = DayOfWeek::from((int) $this->getInternalDateTime()->format('w'));
                }
                return $this->_dow;
            case 'timestamp':
                return (int) $this->epoch_day * JustDate::SECS_PER_DAY;
        }
        return null;
    }

    /**
     * @internal
     */
    public function __isset(mixed $name): bool
    {
        switch ($name) {
            case 'year':
            case 'month':
            case 'day':
            case 'day_of_week':
            case 'timestamp':
                return true;
        }
        return false;
    }

    /**
     * Convert to string
     *
     * Standard string representation is Y-m-d format
     */
    public function __toString(): string
    {
        return $this->getInternalDateTime()->format('Y-m-d');
    }

    /**
     * Create a string representation of the date, with the given format
     *
     * Note that any time values which are requested in the format will always be zero
     *
     * @param  string  $format  The format, as per PHP's date() function
     * @return string The formatted string
     */
    public function format(string $format = 'Y-m-d'): string
    {
        return $this->getInternalDateTime()->format($format);
    }

    /**
     * Get a DateTime object for this date at the specified time in the specified timezone
     *
     * If no time is specified, the DateTime will be set to 00:00:00
     * If no timezone is specified the DateTime will use the system default timezone
     *
     * @param  ?JustTime  $time  Optional time
     * @param  ?DateTimeZone  $timezone  Optional timezone
     * @return DateTime
     */
    public function toDateTime(?JustTime $time = null, ?DateTimeZone $timezone = null)
    {
        $dt = new DateTime;
        if ($timezone) {
            $dt->setTimezone($timezone);
        }
        $dt->setDate($this->year, $this->month, $this->day);
        if ($time) {
            $dt->setTime($time->hours, $time->minutes, $time->seconds, 0);
        } else {
            $dt->setTime(0, 0, 0, 0);
        }
        return $dt;
    }

    /**
     * Get a DateTime object for this date at the specified time in the specified timezone
     *
     * Alias for toDateTime() with a required time. The complementary method is JustTime::onDate().
     * If no timezone is specified the DateTime will use the system default timezone
     *
     * @param  JustTime  $time  The time of day
     * @param  ?DateTimeZone  $timezone  Optional timezone
     */
    public function atTime(JustTime $time, ?DateTimeZone $timezone = null): DateTime
    {
        return $this->toDateTime($time, $timezone);
    }

    /**
     * Add the specified number of days to this date, and return a new JustDate object for the result
     *
     * Note if a negative number of days is supplied then the result will be an earlier date
     * IE $date->addDays(-1) is the same as $date->subDays(1)
     *
     * @param  int  $days  The number of days to add
     * @return JustDate The new JustDate object
     */
    public function addDays(int $days): JustDate
    {
        return new JustDate($this->epoch_day + $days);
    }

    /**
     * Subtract the specified number of days from this date, and return a new JustDate object for the result
     *
     * Note if a negative number of days is supplied then the result will be a later date.
     * IE $date->subDays(-1) is the same as $date->addDays(1)
     *
     * @param  int  $days  The number of days to subtract
     * @return JustDate The new JustDate object
     */
    public function subDays(int $days): JustDate
    {
        return new JustDate($this->epoch_day - $days);
    }

    /**
     * Add the specified number of years, months and days to this date, and return a new JustDate object for the result
     *
     * @param  int  $years  The number of years to add (use negative values to get earlier dates)
     * @param  int  $months  The number of months to add (use negative values to get earlier dates)
     * @param  int  $days  The number of days to add (use negative values to get earlier dates)
     * @return JustDate The new JustDate object
     */
    public function add(int $years, int $months, int $days): JustDate
    {
        return JustDate::make($this->year + $years, $this->month + $months, $this->day + $days);
    }

    /**
     * Add the specified number of weeks to this date, and return a new JustDate object for the result
     *
     * @param  int  $weeks  The number of weeks to add
     * @return JustDate The new JustDate object
     */
    public function addWeeks(int $weeks): JustDate
    {
        return $this->add(0, 0, $weeks * 7);
    }

    /**
     * Subtract the specified number of weeks from this date, and return a new JustDate object for the result
     *
     * @param  int  $weeks  The number of weeks to subtract
     * @return JustDate The new JustDate object
     */
    public function subWeeks(int $weeks): JustDate
    {
        return $this->add(0, 0, $weeks * -7);
    }

    /**
     * Add the specified number of months to this date, and return a new JustDate object for the result
     *
     * @param  int  $months  The number of months to add
     * @return JustDate The new JustDate object
     */
    public function addMonths(int $months): JustDate
    {
        return $this->add(0, $months, 0);
    }

    /**
     * Subtract the specified number of months from this date, and return a new JustDate object for the result
     *
     * @param  int  $months  The number of months to subtract
     * @return JustDate The new JustDate object
     */
    public function subMonths(int $months): JustDate
    {
        return $this->add(0, -$months, 0);
    }

    /**
     * Add the specified number of years to this date, and return a new JustDate object for the result
     *
     * @param  int  $years  The number of years to add
     * @return JustDate The new JustDate object
     */
    public function addYears(int $years): JustDate
    {
        return $this->add($years, 0, 0);
    }

    /**
     * Subtract the specified number of years from this date, and return a new JustDate object for the result
     *
     * @param  int  $years  The number of years to subtract
     * @return JustDate The new JustDate object
     */
    public function subYears(int $years): JustDate
    {
        return $this->add(-$years, 0, 0);
    }

    /**
     * Get the next day after this one
     *
     * @return JustDate The new JustDate object
     */
    public function nextDay(): JustDate
    {
        return $this->addDays(1);
    }

    /**
     * Get the day prior to this one
     *
     * @return JustDate The new JustDate object
     */
    public function prevDay(): JustDate
    {
        return $this->subDays(1);
    }

    /**
     * Get the date which is the first day of this date's week
     *
     * By default weeks are assumed to 'start' on a Monday (so Sunday is the final day).
     * This can be overridden with the optional $week_starts_on parameter.
     *
     * @return JustDate The new JustDate object
     */
    public function startOfWeek(DayOfWeek $week_starts_on = DayOfWeek::Monday): JustDate
    {
        return $this->subDays($this->day_of_week->numDaysSince($week_starts_on));
    }

    /**
     * Get the date which is the final day of this date's week
     *
     * By default weeks are assumed to 'start' on a Monday (so Sunday is the final day).
     * This can be overridden with the optional $week_starts_on parameter.
     *
     * @return JustDate The new JustDate object
     */
    public function endOfWeek(DayOfWeek $week_starts_on = DayOfWeek::Monday): JustDate
    {
        $week_ends_on = $week_starts_on->addDays(6);
        return $this->addDays($this->day_of_week->numDaysUntil($week_ends_on));
    }

    /**
     * Get the date which is the first day of this date's month
     *
     * @return JustDate The new JustDate object
     */
    public function startOfMonth(): JustDate
    {
        return JustDate::make($this->year, $this->month, 1);
    }

    /**
     * Get the date which is the final day of this date's month
     *
     * @return JustDate The new JustDate object
     */
    public function endOfMonth(): JustDate
    {
        return JustDate::make($this->year, $this->month + 1, 0);
    }

    /**
     * Get the MonthAndYear object for the month containing this date
     *
     * @return MonthAndYear The new MonthAndYear object
     */
    public function monthAndYear(): MonthAndYear
    {
        return MonthAndYear::fromDate($this);
    }

    /**
     * Add the given number of dates which pass the test function
     * Typical use is to add a number of 'working days' to a date, where the test function identifies the 'working' dates
     * Note if $num_to_add is zero (or negative) the behaviour is to advance to the first date that does pass the test and return it
     *
     * @param  int  $num_to_add  The number of days to add.
     * @param  callable(JustDate): bool  $test_fn  Function for testing whether or not this date counts for reducing $num_to_add
     */
    public function addDaysPassingTest(int $num_to_add, callable $test_fn): JustDate
    {
        $curr = $this;
        while (! $test_fn($curr)) {
            $curr = $curr->nextDay();
        }
        while ($num_to_add > 0) {
            $curr = $curr->nextDay();
            if ($test_fn($curr)) {
                $num_to_add--;
            }
        }
        return $curr;
    }

    /**
     * Add the given number of working days to the date, where a working day is assumed to be Mon to Fri
     *
     * Note if $num_to_add is zero (or negative) the first working date equal or later than $this is returned
     * If a different definition of 'working day' is required, use JustDate::addDaysPassingTest() with a custom test function
     *
     * @param  int  $num_to_add  The number of 'working' days to add.
     * @param  ?BaseDateSet  $holidays  Optionally provide a set of holiday dates that will not be counted as working days
     */
    public function addWorkingDays(int $num_to_add, ?BaseDateSet $holidays = null): JustDate
    {
        if (($holidays instanceof BaseDateSet) && ! $holidays->isEmpty()) {
            $test_fn = function (JustDate $date) use ($holidays): bool {
                return !($date->isWeekend() || $holidays->includes($date));
            };
        } else {
            $test_fn = function (JustDate $date): bool {
                return ! $date->isWeekend();
            };
        }
        return $this->addDaysPassingTest($num_to_add, $test_fn);
    }

    /**
     * Test whether a JustDate object refers to the same date as this one
     *
     * @return bool True if $other is the same date
     */
    public function isSameAs(JustDate $other): bool
    {
        return $this->epoch_day == $other->epoch_day;
    }

    /**
     * Test whether a JustDate object refers to a date before this one
     *
     * @return bool True if $other is before this date
     */
    public function isBefore(JustDate $other): bool
    {
        return $this->epoch_day < $other->epoch_day;
    }

    /**
     * Test whether a JustDate object refers to a date before or equal to this one
     *
     * @return bool True if $other is before or the same as this date
     */
    public function isBeforeOrSameAs(JustDate $other): bool
    {
        return $this->epoch_day <= $other->epoch_day;
    }

    /**
     * Test whether a JustDate object refers to a date after this one
     *
     * @return bool True if $other is after this date
     */
    public function isAfter(JustDate $other): bool
    {
        return $this->epoch_day > $other->epoch_day;
    }

    /**
     * Test whether a JustDate object refers to a date after or equal to this one
     *
     * @return bool True if $other is after or the same as this date
     */
    public function isAfterOrSameAs(JustDate $other): bool
    {
        return $this->epoch_day >= $other->epoch_day;
    }

    /**
     * Is the date a Sunday
     *
     * @return bool True if the date is a Sunday, false otherwise
     */
    public function isSunday(): bool
    {
        return $this->day_of_week == DayOfWeek::Sunday;
    }

    /**
     * Is the date a Monday
     *
     * @return bool True if the date is a Monday, false otherwise
     */
    public function isMonday(): bool
    {
        return $this->day_of_week == DayOfWeek::Monday;
    }

    /**
     * Is the date a Tuesday
     *
     * @return bool True if the date is a Tuesday, false otherwise
     */
    public function isTuesday(): bool
    {
        return $this->day_of_week == DayOfWeek::Tuesday;
    }

    /**
     * Is the date a Wednesday
     *
     * @return bool True if the date is a Wednesday, false otherwise
     */
    public function isWednesday(): bool
    {
        return $this->day_of_week == DayOfWeek::Wednesday;
    }

    /**
     * Is the date a Thursday
     *
     * @return bool True if the date is a Thursday, false otherwise
     */
    public function isThursday(): bool
    {
        return $this->day_of_week == DayOfWeek::Thursday;
    }

    /**
     * Is the date a Friday
     *
     * @return bool True if the date is a Friday, false otherwise
     */
    public function isFriday(): bool
    {
        return $this->day_of_week == DayOfWeek::Friday;
    }

    /**
     * Is the date a Saturday
     *
     * @return bool True if the date is a Saturday, false otherwise
     */
    public function isSaturday(): bool
    {
        return $this->day_of_week == DayOfWeek::Saturday;
    }

    /**
     * Is the date a Weekday (Monday to Friday)
     *
     * @return bool True if the date is a Weekday, false otherwise
     */
    public function isWeekday(): bool
    {
        return $this->day_of_week->isWeekday();
    }

    /**
     * Is the date a Weekend (Saturday or Sunday)
     *
     * @return bool True if the date is a Saturday or Sunday, false otherwise
     */
    public function isWeekend(): bool
    {
        return $this->day_of_week->isWeekend();
    }

    /**
     * Serialize
     *
     * The integer epoch_day completely defines a JustDate object, so it is sufficient for serialization
     *
     * @internal
     *
     * @return array{epoch_day: int}
     */
    public function __serialize(): array
    {
        return ['epoch_day' => $this->epoch_day];
    }

    /**
     * Unserialize
     *
     * @param  array{epoch_day: int}  $data
     *
     * @internal
     */
    public function __unserialize(array $data)
    {
        $this->epoch_day = (int) $data['epoch_day'];
        $this->_date = null;
    }

    /**
     * Json serialize to the Y-m-d string
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
        return [DateRange::make($this, $this)];
    }
}
