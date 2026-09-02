# JustDate

Class JustDate

Class representing a single date with no time information

* Full name: `\MadisonSolutions\JustDate\JustDate`
* Implements: [`\MadisonSolutions\JustDate\DateRangeList`](./DateRangeList.md), `\JsonSerializable`

## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
| `SECS_PER_DAY` | public |  | 86400 |

## Properties

### year

```php
public int $year
```

The year as an integer

***

### month

```php
public int $month
```

The month as an integer (1 = January ... 12 = December)

***

### day

```php
public int $day
```

The day of the month as an integer

***

### day_of_week

```php
public \MadisonSolutions\JustDate\DayOfWeek $day_of_week
```

The day of the week

***

### timestamp

```php
public int $timestamp
```

Unix timestamp corresponding to 00:00:00 on this date in UTC

***

### epoch_day

The number of days since the Unix epoch

```php
public int $epoch_day
```

***

## Methods

### make

Create a new JustDate object from year, month and day

```php
public static make(int $year, int $month, int $day): \MadisonSolutions\JustDate\JustDate
```

Note that once created, the JustDate is immutable, there's no way to alter the internal date.
It is possible to supply numerical values which are outside of the normal ranges and
the internal date value will be adjusted to correspond.
eg supplying 0 for the $day will result in the last day of the previous month.

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$year` | `int` | The Year (full, 4 digit year) |
| `$month` | `int` | The month (1 = January ... 12 = December) |
| `$day` | `int` | The day of the month (first day is 1) |

**Return Value:**

The new JustDate instance

***

### fromEpochDay

Create a new JustDate object from the epoch day

```php
public static fromEpochDay(int $epoch_day): \MadisonSolutions\JustDate\JustDate
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$epoch_day` | `int` | The number of days since the Unix epoch |

**Return Value:**

The new JustDate instance

***

### fromDateTime

Create a new JustDate object from a DateTime object

```php
public static fromDateTime(\DateTime $date): \MadisonSolutions\JustDate\JustDate
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$date` | `\DateTime` | The DateTime object (remains unchanged) |

**Return Value:**

The new JustDate instance

***

### fromTimestamp

Get the date at the specified timestamp

```php
public static fromTimestamp(int $timestamp, ?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\JustDate
```

If a timezone is specified, the date will be whatever the date is in the specified timezone at the specified timestamp
If timezone is omitted, the date will be whatever the date is in the system default timezone at the specified timestamp

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timestamp` | `int` | The timestamp |
| `$timezone` | `?\DateTimeZone` | Optional timezone |

**Return Value:**

The new JustDate instance

***

### today

Get the date that it is today

```php
public static today(?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\JustDate
```

If a timezone is specified, the date will be whatever the date is right now in the specified timezone
If timezone is omitted, the date will be whatever the date is right now in the system default timezone

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timezone` | `?\DateTimeZone` | Optional timezone |

**Return Value:**

The new JustDate instance

***

### yesterday

Get the date that it was yesterday

```php
public static yesterday(?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\JustDate
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timezone` | `?\DateTimeZone` | Optional timezone - if specified the date will one day before whatever the date is right now in the specified timezone |

**Return Value:**

The new JustDate instance

***

### tomorrow

Get the date that it will be tomorrow

```php
public static tomorrow(?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\JustDate
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timezone` | `?\DateTimeZone` | Optional timezone - if specified the date will one day after whatever the date is right now in the specified timezone |

**Return Value:**

The new JustDate instance

***

### fromYmd

Create a new JustDate object from a string in Y-m-d format

```php
public static fromYmd(string $ymd): \MadisonSolutions\JustDate\JustDate
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ymd` | `string` | The date in Y-m-d format, eg '2019-04-21' |

**Return Value:**

The new JustDate instance

***

### parseYmd

Get year month and day integers from a string in Y-m-d format, if valid

```php
public static parseYmd(string $ymd): array{0: int, 1: int, 2: int}
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ymd` | `string` | The date in Y-m-d format, eg '2019-04-21' |

**Return Value:**

Array containing integers [year, month, day]

***

### difference

Return the (signed) number of days between 2 JustDate objects: $a and $b

```php
public static difference(\MadisonSolutions\JustDate\JustDate $a, \MadisonSolutions\JustDate\JustDate $b): int
```

If $a is before $b the return value will be positive
If $a is after $b the return value will be negative
If $a and $b refer to the same date, the return value will be zero

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$a` | `\MadisonSolutions\JustDate\JustDate` | The start date |
| `$b` | `\MadisonSolutions\JustDate\JustDate` | The end date |

**Return Value:**

The number of days from $from to $to

***

### compare

Compare 2 JustDate objects and return an integer to indicate which one is earlier

```php
public static compare(\MadisonSolutions\JustDate\JustDate $a, \MadisonSolutions\JustDate\JustDate $b): int
```

Returns -1, 0 or 1, depending on whether $a is respectively earlier than, the same as, or later than $b
Can be used as the comparison function in PHP sorting functions, for example usort()

If $a is earlier than $b, returns -1
If $a and $b refer to the same date, returns zero
If $a is later than $b, returns 1

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$a` | `\MadisonSolutions\JustDate\JustDate` | The first date |
| `$b` | `\MadisonSolutions\JustDate\JustDate` | The second date |

**Return Value:**

Result of comparison: -1, 0 or 1

***

### earliest

Return the earliest of a set of dates

```php
public static earliest(\MadisonSolutions\JustDate\JustDate $first, \MadisonSolutions\JustDate\JustDate $others): \MadisonSolutions\JustDate\JustDate
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$first` | `\MadisonSolutions\JustDate\JustDate` |  |
| `$others` | `\MadisonSolutions\JustDate\JustDate` |  |

**Return Value:**

The earliest date from $first and $others

***

### latest

Return the latest of a set of dates

```php
public static latest(\MadisonSolutions\JustDate\JustDate $first, \MadisonSolutions\JustDate\JustDate $others): \MadisonSolutions\JustDate\JustDate
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$first` | `\MadisonSolutions\JustDate\JustDate` |  |
| `$others` | `\MadisonSolutions\JustDate\JustDate` |  |

**Return Value:**

The latest date from $first and $others

***

### __toString

Convert to string

```php
public __toString(): string
```

Standard string representation is Y-m-d format

***

### format

Create a string representation of the date, with the given format

```php
public format(string $format = 'Y-m-d'): string
```

Note that any time values which are requested in the format will always be zero

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$format` | `string` | The format, as per PHP's date() function |

**Return Value:**

The formatted string

***

### toDateTime

Get a DateTime object for this date at the specified time in the specified timezone

```php
public toDateTime(?\MadisonSolutions\JustDate\JustTime $time = null, ?\DateTimeZone $timezone = null): \DateTime
```

If no time is specified, the DateTime will be set to 00:00:00
If no timezone is specified the DateTime will use the system default timezone

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$time` | `?\MadisonSolutions\JustDate\JustTime` | Optional time |
| `$timezone` | `?\DateTimeZone` | Optional timezone |

***

### atTime

Get a DateTime object for this date at the specified time in the specified timezone

```php
public atTime(\MadisonSolutions\JustDate\JustTime $time, ?\DateTimeZone $timezone = null): \DateTime
```

Alias for toDateTime() with a required time. The complementary method is JustTime::onDate().
If no timezone is specified the DateTime will use the system default timezone

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$time` | `\MadisonSolutions\JustDate\JustTime` | The time of day |
| `$timezone` | `?\DateTimeZone` | Optional timezone |

***

### addDays

Add the specified number of days to this date, and return a new JustDate object for the result

```php
public addDays(int $days): \MadisonSolutions\JustDate\JustDate
```

Note if a negative number of days is supplied then the result will be an earlier date
IE $date->addDays(-1) is the same as $date->subDays(1)

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$days` | `int` | The number of days to add |

**Return Value:**

The new JustDate object

***

### subDays

Subtract the specified number of days from this date, and return a new JustDate object for the result

```php
public subDays(int $days): \MadisonSolutions\JustDate\JustDate
```

Note if a negative number of days is supplied then the result will be a later date.
IE $date->subDays(-1) is the same as $date->addDays(1)

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$days` | `int` | The number of days to subtract |

**Return Value:**

The new JustDate object

***

### add

Add the specified number of years, months and days to this date, and return a new JustDate object for the result

```php
public add(int $years, int $months, int $days): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$years` | `int` | The number of years to add (use negative values to get earlier dates) |
| `$months` | `int` | The number of months to add (use negative values to get earlier dates) |
| `$days` | `int` | The number of days to add (use negative values to get earlier dates) |

**Return Value:**

The new JustDate object

***

### addWeeks

Add the specified number of weeks to this date, and return a new JustDate object for the result

```php
public addWeeks(int $weeks): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$weeks` | `int` | The number of weeks to add |

**Return Value:**

The new JustDate object

***

### subWeeks

Subtract the specified number of weeks from this date, and return a new JustDate object for the result

```php
public subWeeks(int $weeks): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$weeks` | `int` | The number of weeks to subtract |

**Return Value:**

The new JustDate object

***

### addMonths

Add the specified number of months to this date, and return a new JustDate object for the result

```php
public addMonths(int $months): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$months` | `int` | The number of months to add |

**Return Value:**

The new JustDate object

***

### subMonths

Subtract the specified number of months from this date, and return a new JustDate object for the result

```php
public subMonths(int $months): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$months` | `int` | The number of months to subtract |

**Return Value:**

The new JustDate object

***

### addYears

Add the specified number of years to this date, and return a new JustDate object for the result

```php
public addYears(int $years): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$years` | `int` | The number of years to add |

**Return Value:**

The new JustDate object

***

### subYears

Subtract the specified number of years from this date, and return a new JustDate object for the result

```php
public subYears(int $years): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$years` | `int` | The number of years to subtract |

**Return Value:**

The new JustDate object

***

### nextDay

Get the next day after this one

```php
public nextDay(): \MadisonSolutions\JustDate\JustDate
```

**Return Value:**

The new JustDate object

***

### prevDay

Get the day prior to this one

```php
public prevDay(): \MadisonSolutions\JustDate\JustDate
```

**Return Value:**

The new JustDate object

***

### startOfWeek

Get the date which is the first day of this date's week

```php
public startOfWeek(\MadisonSolutions\JustDate\DayOfWeek $week_starts_on = DayOfWeek::Monday): \MadisonSolutions\JustDate\JustDate
```

By default weeks are assumed to 'start' on a Monday (so Sunday is the final day).
This can be overridden with the optional $week_starts_on parameter.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$week_starts_on` | `\MadisonSolutions\JustDate\DayOfWeek` |  |

**Return Value:**

The new JustDate object

***

### endOfWeek

Get the date which is the final day of this date's week

```php
public endOfWeek(\MadisonSolutions\JustDate\DayOfWeek $week_starts_on = DayOfWeek::Monday): \MadisonSolutions\JustDate\JustDate
```

By default weeks are assumed to 'start' on a Monday (so Sunday is the final day).
This can be overridden with the optional $week_starts_on parameter.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$week_starts_on` | `\MadisonSolutions\JustDate\DayOfWeek` |  |

**Return Value:**

The new JustDate object

***

### startOfMonth

Get the date which is the first day of this date's month

```php
public startOfMonth(): \MadisonSolutions\JustDate\JustDate
```

**Return Value:**

The new JustDate object

***

### endOfMonth

Get the date which is the final day of this date's month

```php
public endOfMonth(): \MadisonSolutions\JustDate\JustDate
```

**Return Value:**

The new JustDate object

***

### addDaysPassingTest

Add the given number of dates which pass the test function
Typical use is to add a number of 'working days' to a date, where the test function identifies the 'working' dates
Note if $num_to_add is zero (or negative) the behaviour is to advance to the first date that does pass the test and return it

```php
public addDaysPassingTest(int $num_to_add, callable $test_fn): \MadisonSolutions\JustDate\JustDate
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$num_to_add` | `int` | The number of days to add. |
| `$test_fn` | `callable` | Function for testing whether or not this date counts for reducing $num_to_add |

***

### addWorkingDays

Add the given number of working days to the date, where a working day is assumed to be Mon to Fri

```php
public addWorkingDays(int $num_to_add, ?\MadisonSolutions\JustDate\BaseDateSet $holidays = null): \MadisonSolutions\JustDate\JustDate
```

Note if $num_to_add is zero (or negative) the first working date equal or later than $this is returned
If a different definition of 'working day' is required, use JustDate::addDaysPassingTest() with a custom test function

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$num_to_add` | `int` | The number of 'working' days to add. |
| `$holidays` | `?\MadisonSolutions\JustDate\BaseDateSet` | Optionally provide a set of holiday dates that will not be counted as working days |

***

### isSameAs

Test whether a JustDate object refers to the same date as this one

```php
public isSameAs(\MadisonSolutions\JustDate\JustDate $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\JustDate` |  |

**Return Value:**

True if $other is the same date

***

### isBefore

Test whether a JustDate object refers to a date before this one

```php
public isBefore(\MadisonSolutions\JustDate\JustDate $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\JustDate` |  |

**Return Value:**

True if $other is before this date

***

### isBeforeOrSameAs

Test whether a JustDate object refers to a date before or equal to this one

```php
public isBeforeOrSameAs(\MadisonSolutions\JustDate\JustDate $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\JustDate` |  |

**Return Value:**

True if $other is before or the same as this date

***

### isAfter

Test whether a JustDate object refers to a date after this one

```php
public isAfter(\MadisonSolutions\JustDate\JustDate $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\JustDate` |  |

**Return Value:**

True if $other is after this date

***

### isAfterOrSameAs

Test whether a JustDate object refers to a date after or equal to this one

```php
public isAfterOrSameAs(\MadisonSolutions\JustDate\JustDate $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\JustDate` |  |

**Return Value:**

True if $other is after or the same as this date

***

### isSunday

Is the date a Sunday

```php
public isSunday(): bool
```

**Return Value:**

True if the date is a Sunday, false otherwise

***

### isMonday

Is the date a Monday

```php
public isMonday(): bool
```

**Return Value:**

True if the date is a Monday, false otherwise

***

### isTuesday

Is the date a Tuesday

```php
public isTuesday(): bool
```

**Return Value:**

True if the date is a Tuesday, false otherwise

***

### isWednesday

Is the date a Wednesday

```php
public isWednesday(): bool
```

**Return Value:**

True if the date is a Wednesday, false otherwise

***

### isThursday

Is the date a Thursday

```php
public isThursday(): bool
```

**Return Value:**

True if the date is a Thursday, false otherwise

***

### isFriday

Is the date a Friday

```php
public isFriday(): bool
```

**Return Value:**

True if the date is a Friday, false otherwise

***

### isSaturday

Is the date a Saturday

```php
public isSaturday(): bool
```

**Return Value:**

True if the date is a Saturday, false otherwise

***

### isWeekday

Is the date a Weekday (Monday to Friday)

```php
public isWeekday(): bool
```

**Return Value:**

True if the date is a Weekday, false otherwise

***

### isWeekend

Is the date a Weekend (Saturday or Sunday)

```php
public isWeekend(): bool
```

**Return Value:**

True if the date is a Saturday or Sunday, false otherwise

***

### jsonSerialize

Json serialize to the Y-m-d string

```php
public jsonSerialize(): string
```

***

> Automatically generated from source code comments using [phpDocumentor](https://www.phpdoc.org/)
