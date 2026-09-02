<?php

namespace MadisonSolutions\JustDate;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Class JustTime
 *
 * Class representing a time of day, with no date or timezone information
 */
class JustTime implements JsonSerializable
{
    /**
     * Create a new JustTime instance from hours, minutes and seconds
     *
     * Note that once created, the JustTime is immutable, there's no way to alter the internal date.
     * It is possible to supply numerical values which are outside of the normal ranges and
     * the internal date value will be adjusted to correspond.
     * eg supplying 10:65:00 will result in 11:05:00
     * eg supplying 26:-10:00 will result in 01:50:00
     *
     * @param  int  $hours  The hours (0 - 23)
     * @param  int  $minutes  The minutes (0 - 59)
     * @param  int  $seconds  The seconds (0 - 59)
     */
    public static function make(int $hours = 0, int $minutes = 0, int $seconds = 0): JustTime
    {
        $seconds_since_midnight = ($seconds) + ($minutes * 60) + ($hours * 60 * 60);
        return new JustTime($seconds_since_midnight);
    }

    /**
     * Create a new JustTime instance from the total number of seconds since midnight
     *
     * Note the hours will wrap around midnight if the total number of seconds is more than a day.
     *
     * @param  int  $seconds_since_midnight  The total number of seconds since midnight
     * @return JustTime The new JustTime instance
     */
    public static function fromSecondsSinceMidnight(int $seconds_since_midnight): JustTime
    {
        return new JustTime($seconds_since_midnight);
    }

    /**
     * Create a new JustTime object from a DateTime object
     *
     * @param  DateTime  $date  The DateTime object (remains unchanged)
     * @return JustTime The new JustTime instance
     */
    public static function fromDateTime(DateTime $date): JustTime
    {
        return JustTime::make((int) $date->format('H'), (int) $date->format('i'), (int) $date->format('s'));
    }

    /**
     * Get the current time
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone - if specified the time will be whatever the time is right now in the specified timezone
     * @return JustTime The new JustTime instance
     */
    public static function now(?DateTimeZone $timezone = null): JustTime
    {
        $dt = new DateTime;
        if ($timezone) {
            $dt->setTimezone($timezone);
        }
        return JustTime::fromDateTime($dt);
    }

    /**
     * Get the time at the specified timestamp
     *
     * @param  ?DateTimeZone  $timezone  Optional timezone - if specified the time will be whatever the time is in the specified timezone at the specified timestamp
     * @return JustTime The new JustTime instance
     */
    public static function fromTimestamp(int $timestamp, ?DateTimeZone $timezone = null): JustTime
    {
        $dt = new DateTime;
        if ($timezone) {
            $dt->setTimezone($timezone);
        }
        $dt->setTimestamp($timestamp);
        return JustTime::fromDateTime($dt);
    }

    /**
     * Create a new JustTime object from a string in H:i:s format
     *
     * @param  string  $his  The date in H:i:s format, eg '14:35:02' (note seconds can be omitted eg '14:35')
     * @return JustTime The new JustTime instance
     */
    public static function fromHis(string $his): JustTime
    {
        return JustTime::make(...JustTime::parseHis($his));
    }

    /**
     * Get hours minutes and seconds integers from a string in H:i:s format, if valid
     *
     * @param  string  $his  The date in H:i:s format, eg '14:35' (note seconds can be omitted eg '14:35')
     * @return array{0: int, 1: int, 2: int} Array containing integers [year, month, day]
     *
     * @throws InvalidArgumentException If the string does not contain a valid date in Y-m-d format
     */
    public static function parseHis(string $his): array
    {
        if (preg_match('/^(\d\d?):(\d\d?)(:(\d\d?))?$/', trim($his), $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) ($matches[4] ?? 0);
            if ($hours >= 0 && $hours < 24 && $minutes >= 0 && $minutes < 60 && $seconds >= 0 && $seconds < 60) {
                return [$hours, $minutes, $seconds];
            }
        }
        throw new InvalidArgumentException("Invalid H:i:s time '{$his}'");
    }

    /**
     * Return the earliest of a set of times
     *
     * @return JustTime The earliest time from $first and $others
     */
    public static function earliest(JustTime $first, JustTime ...$others): JustTime
    {
        $earliest = $first;
        foreach ($others as $time) {
            if ($time->isBefore($earliest)) {
                $earliest = $time;
            }
        }
        return $earliest;
    }

    /**
     * Return the latest of a set of times
     *
     * @return JustTime The latest time from $first and $others
     */
    public static function latest(JustTime $first, JustTime ...$others): JustTime
    {
        $latest = $first;
        foreach ($others as $time) {
            if ($time->isAfter($latest)) {
                $latest = $time;
            }
        }
        return $latest;
    }

    /**
     * Get the hours, minutes and seconds given the total number of seconds since midnight
     *
     * Note the hours will wrap around midnight if the total number of seconds is more than a day.
     * The hours returned will always be in the interval 0-23.
     * The return value will be an array of integers [0 => hours, 1 => minutes, 2 => seconds]
     *
     * @param  int  $seconds_since_midnight  The total number of seconds since midnight
     * @return array{0: int, 1: int, 2: int} The number of hours, minutes and seconds
     */
    public static function split(int $seconds_since_midnight): array
    {
        // Utility fn for finding the remainder when $a is divided by $b
        // As a side-effect, the quotient is assigned to the 3rd parameter
        $remainder = function (int $a, int $b, &$q) {
            $q = intval($a < 0 ? -ceil(-$a / $b) : floor($a / $b));
            return $a - ($b * $q);
        };
        $secs = $remainder($seconds_since_midnight, 60, $mins);
        $mins = $remainder($mins, 60, $hours);
        $hours = $remainder($hours, 24, $days);
        return [$hours, $mins, $secs];
    }

    /**
     * The number of seconds from midnight to this time
     */
    public readonly int $since_midnight;

    /**
     * Hours, from 0 to 23, as an integer
     */
    public readonly int $hours;

    /**
     * Minutes from 0 to 59, as an integer
     */
    public readonly int $minutes;

    /**
     * Seconds, from 0 to 59, as an integer
     */
    public readonly int $seconds;

    /**
     * DateTime object created and used internally when required for formatting
     */
    protected ?DateTime $_date;

    /**
     * JustTime constructor.
     */
    protected function __construct(int $seconds_since_midnight)
    {
        [$this->hours, $this->minutes, $this->seconds] = JustTime::split($seconds_since_midnight);
        $this->since_midnight = ($this->hours * 60 * 60) + ($this->minutes * 60) + ($this->seconds);
        $this->_date = null;
    }

    /**
     * Get the internal DateTime object for 00:00 on this date (UTC)
     * Creates the DateTime object if it doesn't already exists
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
            $this->_date->setTimestamp($this->since_midnight);
        }
        return $this->_date;
    }

    /**
     * Standard string representation is H:i:s format
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Create a string representation of the time, with the given format
     *
     * Note that any date values which are requested in the format will have values from the Unix epoch - Jan 1st 1970
     *
     * @param  string  $format  The format, as per PHP's date() function
     * @return string The formatted string
     */
    public function format(string $format = 'H:i:s'): string
    {
        return $this->getInternalDateTime()->format($format);
    }

    /**
     * Get a DateTime object for this time on the specified date in the specified timezone
     *
     * Complementary method to JustDate::atTime() - $time->onDate($date) is equivalent to $date->atTime($time).
     * If no timezone is specified the DateTime will use the system default timezone
     *
     * @param  JustDate  $date  The date
     * @param  ?DateTimeZone  $timezone  Optional timezone
     */
    public function onDate(JustDate $date, ?DateTimeZone $timezone = null): DateTime
    {
        return $date->toDateTime($this, $timezone);
    }

    /**
     * Add the specified number of hours, minutes and seconds to this time, and return a new JustTime object for the result
     *
     * Note values will wrap around midnight. Eg if you add 2 hours to 23:30:00 you'll get 01:30:00.
     * (This implies that sometimes adding positive values can lead to a time which is considered 'before' the original)
     * Note any of the values can be negative to subtract that amount of time instead of adding
     *
     * @param  int  $hours  The number of hours to add
     * @param  int  $minutes  The number of minutes to add
     * @param  int  $seconds  The number of seconds to add
     * @return JustTime The new JustTime object
     */
    public function addTime(int $hours = 0, int $minutes = 0, int $seconds = 0): JustTime
    {
        return JustTime::make($this->hours + $hours, $this->minutes + $minutes, $this->seconds + $seconds);
    }

    /**
     * Test whether a JustTime object refers to the same time as this one
     *
     * @return bool True if $other is the same time
     */
    public function isSameAs(JustTime $other): bool
    {
        return $this->since_midnight == $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time before this one
     *
     * @return bool True if $other is before this time
     */
    public function isBefore(JustTime $other): bool
    {
        return $this->since_midnight < $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time before or equal to this one
     *
     * @return bool True if $other is before or the same as this date
     */
    public function isBeforeOrSameAs(JustTime $other): bool
    {
        return $this->since_midnight <= $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time after this one
     *
     * @return bool True if $other is after this date
     */
    public function isAfter(JustTime $other): bool
    {
        return $this->since_midnight > $other->since_midnight;
    }

    /**
     * Test whether a JustTime object refers to a time after or equal to this one
     *
     * @return bool True if $other is after or the same as this time
     */
    public function isAfterOrSameAs(JustTime $other): bool
    {
        return $this->since_midnight >= $other->since_midnight;
    }

    /**
     * Round a time to a given interval
     *
     * For example to round 09:47 to the nearest 15 minutes:
     * $time = (JustTime::make(9, 47))->round(15 * 60); // 09:45
     *
     * @param  int  $interval_seconds  The length of the interval to round to, in seconds
     * @return JustTime A new JustTime instance with the rounded time
     */
    public function round(int $interval_seconds): JustTime
    {
        $rounded = round($this->since_midnight / $interval_seconds) * $interval_seconds;
        return self::fromSecondsSinceMidnight((int) $rounded);
    }

    /**
     * Serialize
     *
     * The integer since_midnight completely defines a JustTime object, so it is sufficient for serialization
     *
     * @internal
     *
     * @return array{since_midnight: int}
     */
    public function __serialize(): array
    {
        return ['since_midnight' => $this->since_midnight];
    }

    /**
     * Unserialize
     *
     * @internal
     *
     * @param  array{since_midnight: int}  $data
     */
    public function __unserialize(array $data)
    {
        $this->__construct((int) $data['since_midnight']);
    }

    /**
     * Json serialize to the H:i:s string
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
