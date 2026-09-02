# MonthAndYear

Class MonthAndYear

Class representing a particular month in a particular year, with no day or time information

* Full name: `\MadisonSolutions\JustDate\MonthAndYear`
* Implements: [`\MadisonSolutions\JustDate\DateRangeList`](./DateRangeList.md), `\JsonSerializable`

## Properties

### year

The year as an integer

```php
public int $year
```

***

### month

The month as an integer (1 = January ... 12 = December)

```php
public int $month
```

***

## Methods

### make

Create a new MonthAndYear object from year and month

```php
public static make(int $year, int $month): \MadisonSolutions\JustDate\MonthAndYear
```

Note that once created, the MonthAndYear is immutable, there's no way to alter the internal values.
It is possible to supply a month value which is outside of the normal 1 - 12 range and
the year and month will be adjusted to correspond.
eg supplying 0 for the $month will result in December of the previous year.

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$year` | `int` | The Year (full, 4 digit year) |
| `$month` | `int` | The month (1 = January ... 12 = December) |

**Return Value:**

The new MonthAndYear instance

***

### fromYm

Create a new MonthAndYear object from a string in Y-m format

```php
public static fromYm(string $ym): \MadisonSolutions\JustDate\MonthAndYear
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ym` | `string` | The month in Y-m format, eg '2019-04' |

**Return Value:**

The new MonthAndYear instance

***

### parseYm

Get year and month integers from a string in Y-m format, if valid

```php
public static parseYm(string $ym): array{0: int, 1: int}
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ym` | `string` | The month in Y-m format, eg '2019-04' |

**Return Value:**

Array containing integers [year, month]

***

### fromDate

Create a new MonthAndYear object containing the given date

```php
public static fromDate(\MadisonSolutions\JustDate\JustDate $date): \MadisonSolutions\JustDate\MonthAndYear
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$date` | `\MadisonSolutions\JustDate\JustDate` | The date |

**Return Value:**

The new MonthAndYear instance

***

### fromDateTime

Create a new MonthAndYear object from a DateTime object

```php
public static fromDateTime(\DateTime $date): \MadisonSolutions\JustDate\MonthAndYear
```

The month and year will be taken from the DateTime object in its own timezone

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$date` | `\DateTime` | The DateTime object (remains unchanged) |

**Return Value:**

The new MonthAndYear instance

***

### thisMonth

Get the current month

```php
public static thisMonth(?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\MonthAndYear
```

If a timezone is specified, the month will be whatever the month is right now in the specified timezone
If timezone is omitted, the month will be whatever the month is right now in the system default timezone

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timezone` | `?\DateTimeZone` | Optional timezone |

**Return Value:**

The new MonthAndYear instance

***

### lastMonth

Get the previous month

```php
public static lastMonth(?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\MonthAndYear
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timezone` | `?\DateTimeZone` | Optional timezone - if specified the month will be one month before whatever the month is right now in the specified timezone |

**Return Value:**

The new MonthAndYear instance

***

### nextMonth

Get the next month

```php
public static nextMonth(?\DateTimeZone $timezone = null): \MadisonSolutions\JustDate\MonthAndYear
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timezone` | `?\DateTimeZone` | Optional timezone - if specified the month will be one month after whatever the month is right now in the specified timezone |

**Return Value:**

The new MonthAndYear instance

***

### difference

Return the (signed) number of months between 2 MonthAndYear objects: $a and $b

```php
public static difference(\MadisonSolutions\JustDate\MonthAndYear $a, \MadisonSolutions\JustDate\MonthAndYear $b): int
```

If $a is before $b the return value will be positive
If $a is after $b the return value will be negative
If $a and $b refer to the same month, the return value will be zero

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$a` | `\MadisonSolutions\JustDate\MonthAndYear` | The start month |
| `$b` | `\MadisonSolutions\JustDate\MonthAndYear` | The end month |

**Return Value:**

The number of months from $a to $b

***

### compare

Compare 2 MonthAndYear objects for sorting purposes

```php
public static compare(\MadisonSolutions\JustDate\MonthAndYear $a, \MadisonSolutions\JustDate\MonthAndYear $b): int
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$a` | `\MadisonSolutions\JustDate\MonthAndYear` | The first month |
| `$b` | `\MadisonSolutions\JustDate\MonthAndYear` | The second month |

**Return Value:**

Negative if $a is before $b, positive if $a is after $b, zero if they are the same month

***

### earliest

Return the earliest of a set of months

```php
public static earliest(\MadisonSolutions\JustDate\MonthAndYear $first, \MadisonSolutions\JustDate\MonthAndYear $others): \MadisonSolutions\JustDate\MonthAndYear
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$first` | `\MadisonSolutions\JustDate\MonthAndYear` | The first month |
| `$others` | `\MadisonSolutions\JustDate\MonthAndYear` | Any number of other months |

**Return Value:**

The earliest month from the set

***

### latest

Return the latest of a set of months

```php
public static latest(\MadisonSolutions\JustDate\MonthAndYear $first, \MadisonSolutions\JustDate\MonthAndYear $others): \MadisonSolutions\JustDate\MonthAndYear
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$first` | `\MadisonSolutions\JustDate\MonthAndYear` | The first month |
| `$others` | `\MadisonSolutions\JustDate\MonthAndYear` | Any number of other months |

**Return Value:**

The latest month from the set

***

### __toString

Standard string representation is Y-m format

```php
public __toString(): string
```

***

### format

Create a string representation of the month, with the given format

```php
public format(string $format = 'Y-m'): string
```

Note that any day or time values which are requested in the format will be those of 00:00:00 on the first day of the month

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$format` | `string` | The format, as per PHP's date() function |

**Return Value:**

The formatted string

***

### firstDate

Get the first date of this month

```php
public firstDate(): \MadisonSolutions\JustDate\JustDate
```

**Return Value:**

The new JustDate object

***

### lastDate

Get the last date of this month

```php
public lastDate(): \MadisonSolutions\JustDate\JustDate
```

**Return Value:**

The new JustDate object

***

### range

Get the range of dates spanning this month

```php
public range(): \MadisonSolutions\JustDate\DateRange
```

**Return Value:**

The new DateRange object

***

### numDays

Get the number of days in this month

```php
public numDays(): int
```

**Return Value:**

The number of days

***

### addMonths

Add the specified number of months and return the new MonthAndYear object

```php
public addMonths(int $months): \MadisonSolutions\JustDate\MonthAndYear
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$months` | `int` | The number of months to add (can be negative) |

**Return Value:**

The new MonthAndYear object

***

### subMonths

Subtract the specified number of months and return the new MonthAndYear object

```php
public subMonths(int $months): \MadisonSolutions\JustDate\MonthAndYear
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$months` | `int` | The number of months to subtract (can be negative) |

**Return Value:**

The new MonthAndYear object

***

### addYears

Add the specified number of years and return the new MonthAndYear object

```php
public addYears(int $years): \MadisonSolutions\JustDate\MonthAndYear
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$years` | `int` | The number of years to add (can be negative) |

**Return Value:**

The new MonthAndYear object

***

### subYears

Subtract the specified number of years and return the new MonthAndYear object

```php
public subYears(int $years): \MadisonSolutions\JustDate\MonthAndYear
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$years` | `int` | The number of years to subtract (can be negative) |

**Return Value:**

The new MonthAndYear object

***

### next

Get the month after this one

```php
public next(): \MadisonSolutions\JustDate\MonthAndYear
```

**Return Value:**

The new MonthAndYear object

***

### prev

Get the month before this one

```php
public prev(): \MadisonSolutions\JustDate\MonthAndYear
```

**Return Value:**

The new MonthAndYear object

***

### includes

Test whether the given date falls within this month

```php
public includes(\MadisonSolutions\JustDate\JustDate $date): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$date` | `\MadisonSolutions\JustDate\JustDate` | The date to test |

**Return Value:**

True if the date is in this month, false otherwise

***

### isSameAs

Test whether this month is the same as another

```php
public isSameAs(\MadisonSolutions\JustDate\MonthAndYear $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\MonthAndYear` | The other month to compare to |

**Return Value:**

True if the months are the same

***

### isBefore

Test whether this month is before another

```php
public isBefore(\MadisonSolutions\JustDate\MonthAndYear $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\MonthAndYear` | The other month to compare to |

**Return Value:**

True if this month is before the other

***

### isBeforeOrSameAs

Test whether this month is before or the same as another

```php
public isBeforeOrSameAs(\MadisonSolutions\JustDate\MonthAndYear $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\MonthAndYear` | The other month to compare to |

**Return Value:**

True if this month is before or the same as the other

***

### isAfter

Test whether this month is after another

```php
public isAfter(\MadisonSolutions\JustDate\MonthAndYear $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\MonthAndYear` | The other month to compare to |

**Return Value:**

True if this month is after the other

***

### isAfterOrSameAs

Test whether this month is after or the same as another

```php
public isAfterOrSameAs(\MadisonSolutions\JustDate\MonthAndYear $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\MonthAndYear` | The other month to compare to |

**Return Value:**

True if this month is after or the same as the other

***

### jsonSerialize

Json serialize to the Y-m string

```php
public jsonSerialize(): string
```

***

> Automatically generated from source code comments using [phpDocumentor](https://www.phpdoc.org/)
