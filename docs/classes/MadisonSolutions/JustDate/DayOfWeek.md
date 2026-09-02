# DayOfWeek

Enum DayOfWeek

* Full name: `\MadisonSolutions\JustDate\DayOfWeek`
* Backed by: `int`

## Cases

| Case | Value | Description |
|------|-------|-------------|
| `Sunday` | `0` |  |
| `Monday` | `1` |  |
| `Tuesday` | `2` |  |
| `Wednesday` | `3` |  |
| `Thursday` | `4` |  |
| `Friday` | `5` |  |
| `Saturday` | `6` |  |

## Methods

### isWeekday

Is this a weekday (mon - fri)?

```php
public isWeekday(): bool
```

***

### isWeekend

Is this a weekend day (sat or sun)?

```php
public isWeekend(): bool
```

***

### addDays

Return the new DayOfWeek after adding $num days

```php
public addDays(int $num): \MadisonSolutions\JustDate\DayOfWeek
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$num` | `int` |  |

***

### subDays

Return the new DayOfWeek after subtracting $num days

```php
public subDays(int $num): \MadisonSolutions\JustDate\DayOfWeek
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$num` | `int` |  |

***

### numDaysUntil

Return the number of days, counting forward from this DayOfWeek, until the next instance of the specified DayOfWeek

```php
public numDaysUntil(\MadisonSolutions\JustDate\DayOfWeek $to): int
```

Returns zero if the specified DayOfWeek is the same as this one

For example DayOfWeek::Sunday->numDaysUntil(DayOfWeek::Monday) is 1
DayOfWeek::Monday->numDaysUntil(DayOfWeek::Sunday) is 6

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$to` | `\MadisonSolutions\JustDate\DayOfWeek` | The target DayOfWeek |

**Return Value:**

The number of days until the target DayOfWeek

***

### numDaysSince

Return the number of days, counting backwards from this DayOfWeek, until the previous instance of the specified DayOfWeek

```php
public numDaysSince(\MadisonSolutions\JustDate\DayOfWeek $from): int
```

Returns zero if the specified DayOfWeek is the same as this one

For example DayOfWeek::Sunday->numDaysSince(DayOfWeek::Monday) is 6
DayOfWeek::Monday->numDaysUntil(DayOfWeek::Sunday) is 1

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$from` | `\MadisonSolutions\JustDate\DayOfWeek` | The target DayOfWeek |

**Return Value:**

The number of days until the target DayOfWeek

***

> Automatically generated from source code comments using [phpDocumentor](https://www.phpdoc.org/)
