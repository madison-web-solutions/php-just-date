# DateRangeList

Interface DateRangeList

General interface for things that can be used as a list of DateRanges
This includes:
JustDate objects (a list containing single DateRange starting and ending on the date)
DateRange objects (a list containing a single DateRange)
DateSet objects (the list of included ranges)
MutableDateSet objects (the list of included ranges)

* Full name: `\MadisonSolutions\JustDate\DateRangeList`

## Methods

### getRanges

Get the DateRange objects associated with this DateRangeList

```php
public getRanges(): \MadisonSolutions\JustDate\DateRange[]
```

**Return Value:**

An array of DateRange objects

***

> Automatically generated from source code comments using [phpDocumentor](https://www.phpdoc.org/)
