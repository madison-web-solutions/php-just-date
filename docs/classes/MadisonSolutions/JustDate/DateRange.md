# DateRange

Class DateRange

Class representing a range of dates
Ranges that contain a single date are allowed (IE the start and end date are the same)
Ranges that contain no dates are impossible

* Full name: `\MadisonSolutions\JustDate\DateRange`
* Implements: [`\MadisonSolutions\JustDate\DateRangeList`](./DateRangeList.md), `\JsonSerializable`

## Properties

### start

The start of the range

```php
public \MadisonSolutions\JustDate\JustDate $start
```

***

### end

The end of the range

```php
public \MadisonSolutions\JustDate\JustDate $end
```

***

### inner_length

The length of the range in days, measuring from the middle of $this->start to the middle of $this->end
So if $start and $end are the same date (shortest possible DateRange), $inner_length will be zero

```php
public int $inner_length
```

***

### outer_length

The length of the range in days, measuring from the start of $this->start to the end of $this->end
So if $start and $end are the same date (shortest possible DateRange), $outer_length will be one

```php
public positive-int $outer_length
```

***

## Methods

### make

Create a new DateRange object from start and end dates

```php
public static make(\MadisonSolutions\JustDate\JustDate $start, \MadisonSolutions\JustDate\JustDate $end): \MadisonSolutions\JustDate\DateRange
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$start` | `\MadisonSolutions\JustDate\JustDate` | Start of range |
| `$end` | `\MadisonSolutions\JustDate\JustDate` | End of range |

***

### eitherWayRound

Create a new DateRange objects from start and end dates specified in any order

```php
public static eitherWayRound(\MadisonSolutions\JustDate\JustDate $a, \MadisonSolutions\JustDate\JustDate $b): \MadisonSolutions\JustDate\DateRange
```

The start date will be whichever of the 2 dates is earliest and the end date
whichever of the 2 dates is latest.

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$a` | `\MadisonSolutions\JustDate\JustDate` | Start or end of range |
| `$b` | `\MadisonSolutions\JustDate\JustDate` | Other side of range |

**Return Value:**

The DateRange object

***

### fromYmd

Create a new DateRange object from start and end date as Y-m-d formatted strings

```php
public static fromYmd(string $start, string $end): \MadisonSolutions\JustDate\DateRange
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$start` | `string` | Start of range, in Y-m-d format |
| `$end` | `string` | End of range, in Y-m-d format |

**Return Value:**

The DateRange object

***

### fromStartAndInnerLength

Create a new DateRange object by specifying the start date and the inner length of the range

```php
public static fromStartAndInnerLength(\MadisonSolutions\JustDate\JustDate $start, int $inner_length): \MadisonSolutions\JustDate\DateRange
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$start` | `\MadisonSolutions\JustDate\JustDate` | Start of range |
| `$inner_length` | `int` |  |

**Return Value:**

The DateRange object

***

### fromStartAndOuterLength

Create a new DateRange object by specifying the start date and the outer length of the range

```php
public static fromStartAndOuterLength(\MadisonSolutions\JustDate\JustDate $start, positive-int $outer_length): \MadisonSolutions\JustDate\DateRange
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$start` | `\MadisonSolutions\JustDate\JustDate` | Start of range |
| `$outer_length` | `positive-int` | The desired outer length of the range |

**Return Value:**

The DateRange object

***

### fromStartAndDuration

Create a new DateRange object by specifying the start date and the duration of the range in years, months and days

```php
public static fromStartAndDuration(\MadisonSolutions\JustDate\JustDate $start, int $years, int $months, int $days): \MadisonSolutions\JustDate\DateRange
```

Individual components of the duration can be negative, but an exception will be thrown if the total duration is negative.
So for example, it is ok to specify +1 month and -5 days as that will always be a positive total duration.

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$start` | `\MadisonSolutions\JustDate\JustDate` | Start of range |
| `$years` | `int` | The number of years of the duration (default 0) |
| `$months` | `int` | The number of months of the duration (default 0) |
| `$days` | `int` | The number of days of the duration (default 0) |

**Return Value:**

The DateRange object

***

### fromEndAndDuration

Create a new DateRange object by specifying the end date and the (positive) duration of the range in years, months and days

```php
public static fromEndAndDuration(\MadisonSolutions\JustDate\JustDate $end, int $years, int $months, int $days): \MadisonSolutions\JustDate\DateRange
```

Individual components of the duration can be negative, but an exception will be thrown if the total duration is negative.
So for example, it is ok to specify +1 month and -5 days as that will always be a positive total duration.

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$end` | `\MadisonSolutions\JustDate\JustDate` | End of range |
| `$years` | `int` | The number of years of the duration (default 0) |
| `$months` | `int` | The number of months of the duration (default 0) |
| `$days` | `int` | The number of days of the duration (default 0) |

**Return Value:**

The DateRange object

***

### currentMonth

Create a new DateRange object spanning the current month

```php
public static currentMonth(): \MadisonSolutions\JustDate\DateRange
```

Start date will be the first day of the current month and end date will be the last day of the current month

* This method is **static**.

**Return Value:**

The DateRange object

***

### currentWeek

Create a new DateRange object spanning the current week

```php
public static currentWeek(\MadisonSolutions\JustDate\DayOfWeek $week_starts_on = DayOfWeek::Monday): \MadisonSolutions\JustDate\DateRange
```

Returns a DateRange with the first day of the current week as the start date, and the final day of the current week as the end date.
By default, Monday is taken to be the 'first' day of the week, but this can be overridden with the optional $week_starts_on parameter.

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$week_starts_on` | `\MadisonSolutions\JustDate\DayOfWeek` | Optionally specify which day of the week to be considered as the 'first', default is Monday |

**Return Value:**

The DateRange object

***

### currentYear

Create a new DateRange object spanning the current year

```php
public static currentYear(): \MadisonSolutions\JustDate\DateRange
```

Start date will be January 1st of the current year and end date will be December 31st of the current year

* This method is **static**.

**Return Value:**

The DateRange object

***

### intersection

Create a new DateRange object which is the intersection of $r1 and $r2

```php
public static intersection(\MadisonSolutions\JustDate\DateRange $r1, \MadisonSolutions\JustDate\DateRange $r2): ?\MadisonSolutions\JustDate\DateRange
```

If $r1 and $r2 have no intersection and are totally separate, then this function returns null

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$r1` | `\MadisonSolutions\JustDate\DateRange` | The first range |
| `$r2` | `\MadisonSolutions\JustDate\DateRange` | The second range |

**Return Value:**

The intersection DateRange object or null

***

### isSingleDay

Does this range consist of just a single day?
IE start date and end date are the same

```php
public isSingleDay(): bool
```

***

### __toString

Standard string representation is eg '2019-04-21 to 2019-04-25'

```php
public __toString(): string
```

***

### jsonSerialize

Json representation is object with 'start' and 'end' properties

```php
public jsonSerialize(): array{start: string, end: string}
```

***

### each

Get a generator which yields each date in the range (inclusive of end points) as a JustDate object

```php
public each(bool $backwards = false): \Generator<int,\MadisonSolutions\JustDate\JustDate>
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backwards` | `bool` | If true the dates will be returned in reverse order (default false). |

***

### eachExceptLast

Get a generator which yields each date in the range (including start but not end) as a JustDate object

```php
public eachExceptLast(bool $backwards = false): \Generator<int,\MadisonSolutions\JustDate\JustDate>
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backwards` | `bool` | If true the dates will be returned in reverse order, starting with the end date, up to but not including the start date (default false). |

***

### isSameAs

Test whether a particular DateRange is the same as this one - IE has the same start date and same end date

```php
public isSameAs(\MadisonSolutions\JustDate\DateRange $range): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$range` | `\MadisonSolutions\JustDate\DateRange` | The DateRange to compare with |

**Return Value:**

True if the $range has the same start and end dates as this range, false otherwise

***

### includes

Test whether a particular date lies within this range

```php
public includes(\MadisonSolutions\JustDate\JustDate $date): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$date` | `\MadisonSolutions\JustDate\JustDate` | The date to test |

**Return Value:**

True if the date is within this range (including endpoints), false otherwise

***

### contains

Test whether a particular date range is completely contained within this range

```php
public contains(\MadisonSolutions\JustDate\DateRange $range): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$range` | `\MadisonSolutions\JustDate\DateRange` | The range to test |

**Return Value:**

True if $range is completely contained within this range, false otherwise

***

### eachSubRange

Get a generator which splits the range into subranges

```php
public eachSubRange(callable $value_fn, bool $backwards = false): \Generator<int,array{range: \MadisonSolutions\JustDate\DateRange, value: \MadisonSolutions\JustDate\T}>
```

The supplied callback function will be applied to each date in the range,
and consecutive dates for which the callback returns equal values will be
grouped together into a subrange.

This function returns a generator which will yield each of these contiguous
subranges in turn, together with the callback value. The yield values will
be in the format of an array with 'value' and 'range' keys.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$value_fn` | `callable` | Callback used to determine how to delimit the subranges Each subrange will contain dates for which the callback returns the same value. |
| `$backwards` | `bool` | If true the subranges will be returned in reverse order (default false). |

***

> Automatically generated from source code comments using [phpDocumentor](https://www.phpdoc.org/)
