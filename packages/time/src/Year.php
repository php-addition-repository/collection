<?php

declare(strict_types=1);

namespace Par\Time;

use DateTimeImmutable;
use DateTimeInterface;
use Par\Core\Comparable;
use Par\Core\Exception\ClassMismatch;
use Par\Core\Hashable;
use Par\Time\Chrono\ChronoField;
use Par\Time\Chrono\ChronoUnit;
use Par\Time\Exception\InvalidArgumentException;
use Par\Time\Exception\UnsupportedTemporalType;
use Par\Time\Temporal\Temporal;
use Par\Time\Temporal\TemporalAdjuster;
use Par\Time\Temporal\TemporalAdjusters;
use Par\Time\Temporal\TemporalAmount;
use Par\Time\Temporal\TemporalField;
use Par\Time\Temporal\TemporalUnit;

/**
 * A year in the ISO-8601 calendar system, such as 2007.
 *
 * @psalm-immutable
 * @template-implements Comparable<Year>
 */
final class Year implements Hashable, Comparable, Temporal, TemporalAdjuster
{
    private int $value;

    /**
     * Obtains the current year from the system clock in the default time-zone.
     *
     * @return self
     * @psalm-mutation-free
     */
    public static function now(): self
    {
        $now = Factory::now();

        return self::fromNative($now);
    }

    /**
     * Obtains an instance of DayOfWeek from an implementation of the DateTimeInterface.
     *
     * @param DateTimeInterface $dateTime The datetime to convert
     *
     * @return self
     * @psalm-mutation-free
     */
    public static function fromNative(DateTimeInterface $dateTime): self
    {
        return self::of(
            ChronoField::Year()->getFromNative($dateTime)
        );
    }

    /**
     * Obtains an instance of Year.
     *
     * @param int $year The year to represent
     *
     * @return self
     * @psalm-mutation-free
     */
    public static function of(int $year): self
    {
        return new self($year);
    }

    /**
     * Obtains an instance of Year from a text string such as 2007.
     *
     * @param string $text The text to parse
     *
     * @return self
     * @throws InvalidArgumentException If text could not be parsed or value is outside of range
     * @psalm-mutation-free
     */
    public static function parse(string $text): self
    {
        Assert::regex($text, '/^[-+]?\d{1,}$/');

        return new self((int)$text);
    }

    /**
     * Checks if the year is a leap year, according to the ISO calendar system rules.
     *
     * @param int $year
     *
     * @return bool
     * @psalm-mutation-free
     */
    public static function isLeapYear(int $year): bool
    {
        if ($year === 0) {
            return false;
        }
        $dt = Factory::createDate($year);

        return (int)$dt->format('L') === 1;
    }

    /**
     * @inheritDoc
     * @psalm-assert-if-true =Year $other
     */
    public function equals(mixed $other): bool
    {
        if ($other instanceof static) {
            return $this->hash() === $other->hash();
        }

        return false;
    }

    /**
     * @inheritDoc
     *
     * @return int
     */
    public function hash(): int
    {
        return $this->value;
    }

    /**
     * Gets the year value.
     *
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Gets the length of this year in days.
     *
     * @return int 365 or 366
     */
    public function length(): int
    {
        return $this->isLeap() ? 366 : 365;
    }

    /**
     * Checks if the year is a leap year, according to the ISO calendar system rules.
     *
     * @return bool
     * @see Year::isLeapYear
     */
    public function isLeap(): bool
    {
        return static::isLeapYear($this->value);
    }

    /**
     * Outputs this year as a string.
     *
     * @return string A string representation of this year
     */
    public function toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @inheritDoc
     */
    public function compareTo(Comparable $other): int
    {
        if ($other instanceof static) {
            return $this->value <=> $other->value;
        }

        throw ClassMismatch::forExpectedInstance($this, $other);
    }

    /**
     * Checks if this year is after the specified year.
     *
     * @param Year $other The other year to compare to
     *
     * @return bool
     */
    public function isAfter(Year $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Checks if this year is before the specified year.
     *
     * @param Year $year The other year to compare to
     *
     * @return bool
     */
    public function isBefore(Year $year): bool
    {
        return $this->compareTo($year) < 0;
    }

    /**
     * @inheritDoc
     */
    public function minus(int $amountToSubtract, TemporalUnit $unit): self
    {
        return $this->plus($amountToSubtract * -1, $unit);
    }

    /**
     * @inheritDoc
     */
    public function minusAmount(TemporalAmount $amount): self
    {
        /** @var static $temporal */
        $temporal = $amount->subtractFrom($this);

        return $temporal;
    }

    /**
     * Returns a copy of this Year with the specified number of years subtracted.
     *
     * @param int $years The years to subtract, may be negative
     *
     * @return self
     */
    public function minusYears(int $years): self
    {
        return $this->minus($years, ChronoUnit::Years());
    }

    /**
     * @inheritDoc
     */
    public function plus(int $amountToAdd, TemporalUnit $unit): self
    {
        /** @psalm-var TemporalAdjuster<YearMonth> $adjuster */
        $adjuster = TemporalAdjusters::plusUnit($amountToAdd, $unit);

        return self::with($adjuster);
    }

    /**
     * @inheritDoc
     */
    public function with(TemporalAdjuster $adjuster): self
    {
        return $adjuster->adjustInto($this);
    }

    /**
     * @inheritDoc
     */
    public function withField(TemporalField $field, int $newValue): static
    {
        if (!$this->supportsField($field)) {
            throw UnsupportedTemporalType::forField($field);
        }

        return self::of($newValue);
    }

    /**
     * @inheritDoc
     */
    public function adjustInto(Temporal $temporal): Temporal
    {
        if ($temporal instanceof self) {
            return $this;
        }

        $field = ChronoField::Year();

        return $temporal->withField($field, $this->value);
    }

    /**
     * @inheritDoc
     */
    public function plusAmount(TemporalAmount $amount): self
    {
        /** @var static $temporal */
        $temporal = $amount->addTo($this);

        return $temporal;
    }

    /**
     * Returns a copy of this Year with the specified number of years added.
     *
     * @param int $years The years to add, may be negative
     *
     * @return self
     */
    public function plusYears(int $years): self
    {
        return $this->plus($years, ChronoUnit::Years());
    }

    /**
     * @inheritDoc
     */
    public function toNative(): DateTimeImmutable
    {
        return Factory::createDate($this->value);
    }

    /**
     * @inheritDoc
     */
    public function supportsUnit(TemporalUnit $unit): bool
    {
        if ($unit instanceof ChronoUnit) {
            return match ($unit) {
                ChronoUnit::Years(), ChronoUnit::Decades(), ChronoUnit::Centuries(), ChronoUnit::Millennia() => true,
                default => false
            };
        }

        return $unit->isSupportedBy($this);
    }

    /**
     * Checks if the specified field is supported.
     *
     * Supported:
     * - ChronoField::YEAR()
     *
     * @param TemporalField $field The field to check
     *
     * @return bool
     */
    public function supportsField(TemporalField $field): bool
    {
        return ChronoField::Year()->equals($field);
    }

    /**
     * @inheritDoc
     */
    public function get(TemporalField $field): int
    {
        if ($this->supportsField($field)) {
            return $this->value;
        }

        throw UnsupportedTemporalType::forField($field);
    }

    /**
     * @throws InvalidArgumentException If year is outside of range
     */
    private function __construct(int $year)
    {
        ChronoField::Year()->checkValidValue($year);

        $this->value = $year;
    }

}