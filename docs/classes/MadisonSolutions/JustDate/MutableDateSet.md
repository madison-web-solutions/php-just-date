# MutableDateSet

Class MutableDateSet

Class for storing a set of unique dates
Internally represented as a list of sorted, disjoint DateRange objects
Unlike DateSet, the dates that are included in a MutableDateSet can be modified after the object is created

* Full name: `\MadisonSolutions\JustDate\MutableDateSet`
* Parent class: [`\MadisonSolutions\JustDate\BaseDateSet`](./BaseDateSet.md)

## Methods

### sortedRangesFromSingleDates

Utility function that takes any number of JustDate objects and turns them into a normalized list of DateRanges

```php
public static sortedRangesFromSingleDates(\MadisonSolutions\JustDate\JustDate $dates): list<\MadisonSolutions\JustDate\DateRange>
```

Any consecutive dates in the input will be merged into a single range
Any repeated dates will be merged together
The resulting list of ranges will be sorted and disjoint

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$dates` | `\MadisonSolutions\JustDate\JustDate` | The input dates |

**Return Value:**

Resulting normalized list of ranges

***

### __construct

Create a MutableDateSet

```php
public __construct(\MadisonSolutions\JustDate\DateRangeList $lists): mixed
```

The dates that are initially included in the set can be defined by supplying any number of JustDate, DateRange,
DateSet or MutableDateSet objects (or any other class implementing DateRangeList) as parameters.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lists` | `\MadisonSolutions\JustDate\DateRangeList` |  |

***

### includes

Determine whether the given date is a member of this set

```php
public includes(\MadisonSolutions\JustDate\JustDate $date): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$date` | `\MadisonSolutions\JustDate\JustDate` |  |

***

### subtract

Create a new set by subtracting a Date or DateRange or set of dates from this set

```php
public subtract(\MadisonSolutions\JustDate\DateRangeList $list_to_cut): static
```

The dates in the resulting object will be those that are contained in this set but are not contained
in the supplied object. Returns a new set (does not mutate $this)

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$list_to_cut` | `\MadisonSolutions\JustDate\DateRangeList` |  |

***

### isEmpty

Determine whether this set is empty

```php
public isEmpty(): bool
```

**Return Value:**

True if this set is empty (IE contains no dates), false otherwise

***

### getSpanningRange

Fetch the single date range that spans this set

```php
public getSpanningRange(): ?\MadisonSolutions\JustDate\DateRange
```

Fetch the single date range that covers all the dates in this set
IE the returned range will start with the earliest date in this set, and finish with the latest
Returns null in case this set is empty

**Return Value:**

The spanning DateRange, or null if this set is empty

***

### eachRange

Get a generator which yields each range in the set as a DateRange object

```php
public eachRange(bool $backwards = false): \Generator<int,\MadisonSolutions\JustDate\DateRange>
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backwards` | `bool` | If true the ranges will be returned in reverse order (default false). |

***

### eachDate

Get a generator which yields each date in the set as a JustDate object

```php
public eachDate(bool $backwards = false): \Generator<int,\MadisonSolutions\JustDate\JustDate>
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$backwards` | `bool` | If true the dates will be returned in reverse order (default false). |

***

### window

Get a generator which yields whether or not each date in the window range belongs to this set

```php
public window(\MadisonSolutions\JustDate\DateRange $window): \Generator<int,array{0: \MadisonSolutions\JustDate\JustDate, 1: bool}>
```

Specifically, the generator will yield an array for each each date in the window range in order
The first element of the array will be the JustDate object for that date
The second, a boolean, true if the date belongs to this set, and false otherwise.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$window` | `\MadisonSolutions\JustDate\DateRange` |  |

***

### __toString

Get the string representation of this set

```php
public __toString(): string
```

***

### getRanges

Get the normalized list of ranges as a plain PHP array

```php
public getRanges(): list<\MadisonSolutions\JustDate\DateRange>
```

***

### jsonSerialize

Json representation is array of ranges

```php
public jsonSerialize(): list<array{start: string, end: string}>
```

***

### fromString

Unserialize by parsing the standard string representation

```php
public static fromString(string $serialized): static
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$serialized` | `string` |  |

***

### isSameAs

Test whether the given object consists of the exact same set of dates as this one

```php
public isSameAs(\MadisonSolutions\JustDate\DateRangeList $other): bool
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\DateRangeList` | An object implementing DateRangeList to compare with (JustDate, DateRange, DateSet or MutableDateSet) |

**Return Value:**

True if the set of dates in $other is exactly the same as the set of dates in this set, false otherwise

***

### contains

Test whether this set contains all of the dates in the given object

```php
public contains(\MadisonSolutions\JustDate\DateRangeList $other): bool
```

Note: returns true if $other is an empty DateSet or MutableDateSet.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$other` | `\MadisonSolutions\JustDate\DateRangeList` | An object implementing DateRangeList to compare with (JustDate, DateRange, DateSet or MutableDateSet) |

**Return Value:**

True if this set contains all of the dates in $other, false otherwise

***

### fromDates

Alternative way of constructing a MutableDateSet object that is optimised for creating from JustDate objects

```php
public static fromDates(\MadisonSolutions\JustDate\JustDate $dates): \MadisonSolutions\JustDate\MutableDateSet
```

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$dates` | `\MadisonSolutions\JustDate\JustDate` | Dates that should initially be included in the set |

***

### union

Create a new MutableDateSet whose dates are the union of all of the dates in the supplied objects

```php
public static union(\MadisonSolutions\JustDate\DateRangeList $lists): \MadisonSolutions\JustDate\MutableDateSet
```

Note this is functionally identical to the standard new MutableDateSet() constructor and is included just
for code readability and contrast with the complementary MutableDateSet::intersection() function.

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lists` | `\MadisonSolutions\JustDate\DateRangeList` |  |

***

### intersection

Create a new MutableDateSet which is the intersection of the supplied objects

```php
public static intersection(\MadisonSolutions\JustDate\DateRangeList $lists): \MadisonSolutions\JustDate\MutableDateSet
```

The dates in the resulting MutableDateSet will be those dates which are included in every one of the arguments

* This method is **static**.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lists` | `\MadisonSolutions\JustDate\DateRangeList` |  |

***

### add

Add a Date or DateRange or set of dates to this set

```php
public add(\MadisonSolutions\JustDate\DateRangeList $list): $this
```

The dates contained in the supplied object will be added to this set.
Note the set is mutated by this function.
The updated set is returned for chaining.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$list` | `\MadisonSolutions\JustDate\DateRangeList` |  |

***

### addRange

Add the dates in a DateRange to this set

```php
public addRange(\MadisonSolutions\JustDate\DateRange $new): $this
```

The dates contained in the supplied object will be added to this set.
Note the set is mutated by this function.
The updated set is returned for chaining.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$new` | `\MadisonSolutions\JustDate\DateRange` |  |

***

### remove

Remove a Date or DateRange or set of dates from this set

```php
public remove(\MadisonSolutions\JustDate\DateRangeList $list_to_cut): $this
```

The dates contained in the supplied object will be removed from this set.
Note the set is mutated by this function.
The updated set is returned for chaining.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$list_to_cut` | `\MadisonSolutions\JustDate\DateRangeList` |  |

***

> Automatically generated from source code comments using [phpDocumentor](https://www.phpdoc.org/)
