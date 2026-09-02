# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`madison-solutions/just-date` is a dependency-free PHP 8.1+ library (namespace `MadisonSolutions\JustDate`, PSR-4 from `src/`) for dates with no time component (`JustDate`), times with no date component (`JustTime`), contiguous date ranges (`DateRange`) and sets of dates (`DateSet` / `MutableDateSet`). This is version 2; `migration.md` documents the v1 → v2 breaking changes and `CHANGELOG.md` is kept up to date per release (git tags `v2.x.y`).

## Commands

PHP is deliberately not installed on the development machine. Everything runs in Docker via the Makefile, which wraps `docker compose run --rm` against one service per supported PHP version (`php81` ... `php85`, defined in `compose.yaml`, built from `docker/Dockerfile`). Run `make help` for the full target list.

```bash
make build                      # build images for all PHP versions (once, or after editing docker/Dockerfile)
make install                    # composer install
make composer ARGS="update"     # any other composer command

# Tests (PHPUnit 10; there is no phpunit.xml, so the Makefile passes the test dir explicitly)
make test                       # default PHP version is the oldest supported (PHP ?= 8.1 in the Makefile)
make test PHP=8.4               # pick a version
make test-all                   # every version in VERSIONS, continues past failures, prints a summary
make test ARGS="--filter 'DateTest::testAddWorkingDays'"   # single test
make test ARGS="test/DateSetTest.php"                      # single file (appended after `test`)

# Static analysis (PHPStan level 9, src/ only, cache in phpstan-tmp/)
make phpstan
make phpstan-all

# Code style (Laravel Pint, laravel preset with overrides in pint.json)
make pint ARGS=--test           # check
make pint                       # fix

make check                      # test-all + phpstan-all + pint --test
make php ARGS="-r 'echo PHP_VERSION;'"   # one-off php command
make shell PHP=8.5              # bash inside a container

# API docs (phpDocumentor, run from its own image, output to docs/ via phpdoc.dist.xml + the custom Markdown theme in phpdoc-theme/)
make docs                       # regenerate docs/
make docs-check                 # regenerate and fail if docs/ differs from what is committed (part of make check)
```

Every target defaults to the oldest supported version, so code or packages that work there are the most likely to work everywhere. Independently, `composer.json` sets `config.platform.php` to `8.1.0`, so Composer resolves an 8.1-compatible `vendor/` whichever container it runs in. When dropping support for a PHP version, bump `require.php`, `config.platform.php`, `VERSIONS` in the Makefile and the service list in `compose.yaml` together.

Test classes live in `test/` with no namespace and no bootstrap beyond Composer's autoloader.

## Architecture

**Everything is an integer underneath.** `JustDate` stores only `epoch_day` (days since the Unix epoch, a `readonly int`); `JustTime` stores `since_midnight` (seconds). Comparisons, `difference()`, `addDays()`, `nextDay()` etc. are integer arithmetic. A UTC `DateTime` is created lazily (`getInternalDateTime()`, cached in `_date`) only when formatting or calendar math (month/year arithmetic, `year`/`month`/`day` via `__get`) needs it. Keep new features on the integer path where possible and don't let timezones leak in: internal `DateTime` objects are always UTC at 00:00.

**Constructors are protected.** `JustDate`, `JustTime` and `DateRange` are created via static factories (`make()`, `fromYmd()`, `fromDateTime()`, `today()`, `fromEpochDay()`, ...). All three are immutable; mutator-looking methods return new instances. `DateRange::make()` throws `InvalidArgumentException` if start > end (use `eitherWayRound()` when order is unknown).

**`DateRangeList` is the glue interface.** It has a single method, `getRanges(): DateRange[]`, and is implemented by `JustDate` (one single-day range), `DateRange` (itself), and `BaseDateSet`. Any API that accepts "some dates" (set constructors, `union`, `intersection`, `subtract`, `contains`, `isSameAs`, `MutableDateSet::add`) is typed against `DateRangeList`, so a date, a range and a set are interchangeable as inputs.

**Sets are normalized range lists.** `BaseDateSet` holds `protected DateRange[] $ranges` with the invariant that the array is always sorted by start and disjoint, with adjacent ranges merged (see `normalizeRanges()` and `sortedRangesFromSingleDates()`). Every algorithm in `BaseDateSet` (`includes`, `subtract`, `getIntersectingRanges`, `window`, `contains`, `isSameAs`, `__toString`/`fromString`) relies on this invariant, so any code that assigns to `$ranges` must preserve it. `DateSet` is immutable and normalizes once in its constructor; `MutableDateSet` adds `add()`/`addRange()`/`remove()` which splice into the sorted list in place. Note `subtract()` returns a new set on both classes; `remove()` is the in-place variant on `MutableDateSet` only.

**Inner vs outer length.** `DateRange::inner_length` is end − start (number of "nights"); `outer_length` is inner + 1 (number of days). The two static constructors `fromStartAndInnerLength` / `fromStartAndOuterLength` mirror this. `range-lengths.png` in the repo root illustrates it.

**Iteration is via generators**, all with a `bool $backwards = false` parameter: `DateRange::each()`, `eachExceptLast()`, `eachSubRange($value_fn)`; `BaseDateSet::eachRange()`, `eachDate()`, `window($range)`.

**Serialization.** Classes implement `JsonSerializable` and the `__serialize`/`__unserialize` magic methods (not the `Serializable` interface). `JustDate` JSON-encodes to `Y-m-d`, `JustTime` to `H:i:s`, `DateRange` to `{start, end}`, sets to an array of ranges. Sets also round-trip through `__toString()` / `fromString()` using the `2023-10-01 to 2023-10-10, 2023-11-17` format.

**`DayOfWeek`** is an int-backed enum (0 = Sunday ... 6 = Saturday, matching PHP's `w` format) returned by `JustDate::$day_of_week`. Week-based methods (`startOfWeek`, `endOfWeek`, `DateRange::currentWeek`) take a `$week_starts_on` parameter defaulting to Monday.

## Conventions

- Public API changes need a matching entry in `CHANGELOG.md`, updated examples in `README.md` where relevant, and a `make docs` run with the regenerated `docs/` committed. `make check` fails if `docs/` is stale.
- `JustDate` "magic" properties (`year`, `month`, `day`, `day_of_week`, `timestamp`) are declared with `@property-read` on the class docblock and resolved in `__get`/`__isset`; add new ones in all three places so PHPStan level 9 stays green.
- PHPStan only analyses `src/`; tests are type-annotated for PHPStan anyway (e.g. `@param class-string<object>`).
