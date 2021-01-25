<?php

declare(strict_types=1);

namespace Par\TimeTest\Unit;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Par\Core\PHPUnit\HashableAssertions;
use Par\Time\Chrono\ChronoField;
use Par\Time\Chrono\ChronoUnit;
use Par\Time\Exception\InvalidArgumentException;
use Par\Time\Factory;
use Par\Time\Month;
use Par\Time\PHPUnit\TimeTestCaseTrait;
use Par\Time\Temporal\TemporalAmount;
use Par\Time\Year;
use Par\Time\YearMonth;
use PHPUnit\Framework\TestCase;

class YearMonthTest extends TestCase
{
    use TimeTestCaseTrait;
    use HashableAssertions;

    /**
     * @test
     */
    public function itCanBeCreatedFromValue(): void
    {
        $expectedYear = 2011;
        $expectedMonth = 7;
        $monthDay = YearMonth::of($expectedYear, $expectedMonth);

        self::assertSame($expectedYear, $monthDay->yearValue());
        self::assertSame($expectedMonth, $monthDay->monthValue());
    }

    /**
     * @test
     */
    public function itCanBeTransformedToString(): void
    {
        self::assertSame(YearMonth::of(2020, 1)->toString(), '2020-01');
    }

    /**
     * @test
     */
    public function itCanDetermineEqualityWithOther(): void
    {
        $yearMonth = YearMonth::of(2020, 1);
        $otherYearMonth = YearMonth::of(2021, 2);

        self::assertTrue($yearMonth->equals(clone $yearMonth));
        self::assertFalse($yearMonth->equals($otherYearMonth));
        self::assertFalse($yearMonth->equals(null));
    }

    /**
     * @test
     */
    public function itCanUpdateMonth(): void
    {
        $yearMonth = YearMonth::of(2020, 1);

        self::assertHashEquals(
            Month::of(4),
            $yearMonth->withMonth(Month::of(4))->month()
        );
        self::assertHashEquals($yearMonth->year(), $yearMonth->withMonth(Month::of(4))->year());
        self::assertHashNotEquals($yearMonth, $yearMonth->withMonth(Month::of(4)));

        self::assertHashEquals(Month::of(4), $yearMonth->withMonth(4)->month());
        self::assertHashEquals($yearMonth->year(), $yearMonth->withMonth(4)->year());
        self::assertHashNotEquals($yearMonth, $yearMonth->withMonth(4));
    }

    /**
     * @test
     */
    public function itCanUpdateYear(): void
    {
        $yearMonth = YearMonth::of(2020, 1);

        self::assertHashEquals(
            Year::of(1995),
            $yearMonth->withYear(Year::of(1995))->year()
        );
        self::assertHashEquals($yearMonth->month(), $yearMonth->withYear(Year::of(1995))->month());
        self::assertHashNotEquals($yearMonth, $yearMonth->withYear(Year::of(1995)));

        self::assertHashEquals(Year::of(1995), $yearMonth->withYear(1995)->year());
        self::assertHashEquals($yearMonth->month(), $yearMonth->withYear(1995)->month());
        self::assertHashNotEquals($yearMonth, $yearMonth->withYear(1995));
    }

    /**
     * @test
     */
    public function itIsHashable(): void
    {
        self::assertSame(200103, YearMonth::of(2001, 3)->hash());
    }

    /**
     * @return array<string, array{YearMonth, DateTimeInterface}>
     */
    public function provideNative(): array
    {
        return [
            DateTime::class => [
                YearMonth::of(2021, 1),
                DateTime::createFromImmutable(Factory::createDate(2021, 1, 14)),
            ],
            DateTimeImmutable::class => [
                YearMonth::of(2019, 7),
                Factory::createDate(2019, 7, 20),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideNative
     *
     * @param YearMonth         $expected
     * @param DateTimeInterface $native
     *
     * @return void
     */
    public function itCanBeCreatedFromNativeDateTime(YearMonth $expected, DateTimeInterface $native): void
    {
        self::assertHashEquals($expected, YearMonth::fromNative($native));
    }

    /**
     * @test
     */
    public function itCanBeCreatedForCurrentYear(): void
    {
        $this->wrapWithTestNow(
            static function () {
                $now = Factory::now();

                $current = YearMonth::now();

                self::assertSameYear($now, $current->yearValue());
                self::assertSameMonth($now, $current->monthValue());
            }
        );
    }

    /**
     * @return array<string, array{string, YearMonth}>
     */
    public function provideStrings(): array
    {
        return [
            'month < 10' => ['2001-02', YearMonth::of(2001, 2)],
            'month > 10' => ['2001-12', YearMonth::of(2001, 12)],
            'large year' => ['999999-01', YearMonth::of(999999, 1)],
            'small year' => ['1-01', YearMonth::of(1, 1)],
            'zero year' => ['0-01', YearMonth::of(0, 1)],
            'negative year' => ['-2-01', YearMonth::of(-2, 1)],
        ];
    }

    /**
     * @dataProvider provideStrings
     *
     * @param string    $text
     * @param YearMonth $expected
     *
     * @test
     */
    public function itCanBeCreatedFromString(string $text, YearMonth $expected): void
    {
        self::assertHashEquals($expected, YearMonth::parse($text));
    }

    /**
     * @test
     */
    public function ItWillThrowExceptionWhenCreatingFromInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        YearMonth::parse('The year 2000');
    }

    /**
     * @test
     */
    public function itCanBeNaturallySorted(): void
    {
        $list = [
            YearMonth::of(2001, 3),
            YearMonth::of(2010, 1),
            YearMonth::of(2001, 2),
        ];

        uasort(
            $list,
            static function (YearMonth $a, YearMonth $b): int {
                return $a->compareTo($b);
            }
        );

        $orderedList = [];
        foreach ($list as $item) {
            $orderedList[] = $item->toString();
        }

        self::assertSame(
            ['2001-02', '2001-03', '2010-01'],
            $orderedList
        );
    }

    /**
     * @test
     */
    public function itCanDetermineIfItIsAfterAnother(): void
    {
        $current = YearMonth::of(2000, 6);
        $yearAfter = YearMonth::of(2001, 6);
        $yearBefore = YearMonth::of(1999, 6);
        $monthAfter = YearMonth::of(2000, 7);
        $monthBefore = YearMonth::of(2000, 5);

        self::assertTrue($current->isAfter($yearBefore));
        self::assertTrue($current->isAfter($monthBefore));
        self::assertFalse($current->isAfter($current));
        self::assertFalse($current->isAfter($yearAfter));
        self::assertFalse($current->isAfter($monthAfter));
    }

    /**
     * @test
     */
    public function itCanDetermineIfItIsBeforeAnother(): void
    {
        $current = YearMonth::of(2000, 6);
        $yearAfter = YearMonth::of(2001, 6);
        $yearBefore = YearMonth::of(1999, 6);
        $monthAfter = YearMonth::of(2000, 7);
        $monthBefore = YearMonth::of(2000, 5);

        self::assertTrue($current->isBefore($yearAfter));
        self::assertTrue($current->isBefore($monthAfter));
        self::assertFalse($current->isBefore($current));
        self::assertFalse($current->isBefore($yearBefore));
        self::assertFalse($current->isBefore($monthBefore));
    }

    /**
     * @dataProvider provideSupportedFields
     *
     * @param ChronoField $field
     * @param bool        $expected
     */
    public function testCanRetrieveListOfSupportedUnits(ChronoField $field, bool $expected): void
    {
        $source = YearMonth::of(2015, 2);

        self::assertSame($expected, $source->supportsField($field));
    }

    public function testCanRetrieveValueOfUnit(): void
    {
        $source = YearMonth::of(2015, 2);

        self::assertSame($source->yearValue(), $source->get(ChronoField::Year()));
        self::assertSame($source->monthValue(), $source->get(ChronoField::MonthOfYear()));
    }

    public function testItCanBeTransformedToNativeDateTime(): void
    {
        $source = YearMonth::of(2015, 2);

        self::assertEquals(DateTimeImmutable::createFromFormat('Y-m', '2015-02'), $source->toNative());
    }

    public function testCanAddAmount(): void
    {
        $source = YearMonth::of(2015, 2);

        $amount = $this->createMock(TemporalAmount::class);
        $amount->expects($this->once())
               ->method('addTo')
               ->with($source)
               ->willReturn($source);

        $source->plusAmount($amount);
    }

    public function testCanSubtractAmount(): void
    {
        $source = YearMonth::of(2015, 2);

        $amount = $this->createMock(TemporalAmount::class);
        $amount->expects($this->once())
               ->method('subtractFrom')
               ->with($source)
               ->willReturn($source);

        $source->minusAmount($amount);
    }

    public function provideForUnitMath(): array
    {
        return [
            'positive-years' => [YearMonth::of(2015, 3), 2, ChronoUnit::Years(), YearMonth::of(2017, 3)],
            'positive-month' => [YearMonth::of(2015, 3), 2, ChronoUnit::Months(), YearMonth::of(2015, 5)],
            'positive-month-overflow' => [YearMonth::of(2015, 3), 12, ChronoUnit::Months(), YearMonth::of(2016, 3)],
            'negative-years' => [YearMonth::of(2015, 3), -2, ChronoUnit::Years(), YearMonth::of(2013, 3)],
            'negative-month' => [YearMonth::of(2015, 3), -2, ChronoUnit::Months(), YearMonth::of(2015, 1)],
            'negative-month-overflow' => [YearMonth::of(2015, 3), -12, ChronoUnit::Months(), YearMonth::of(2014, 3)],
        ];
    }

    /**
     * @dataProvider provideForUnitMath
     *
     * @param YearMonth  $expected
     * @param int        $amountToSubtract
     * @param ChronoUnit $unitToSubtract
     * @param YearMonth  $source
     */
    public function testCanAddUnit(YearMonth $source,
                                   int $amountToSubtract,
                                   ChronoUnit $unitToSubtract,
                                   YearMonth $expected): void
    {
        self::assertHashEquals($expected, $source->plus($amountToSubtract, $unitToSubtract));
    }

    /**
     * @dataProvider provideForUnitMath
     *
     * @param YearMonth  $expected
     * @param int        $amountToSubtract
     * @param ChronoUnit $unitToSubtract
     * @param YearMonth  $source
     */
    public function testCanSubtractUnit(YearMonth $expected,
                                        int $amountToSubtract,
                                        ChronoUnit $unitToSubtract,
                                        YearMonth $source): void
    {
        self::assertHashEquals($expected, $source->minus($amountToSubtract, $unitToSubtract));
    }

    public function provideForYearMath(): array
    {
        return [
            'positive' => [YearMonth::of(2015, 3), 2, YearMonth::of(2017, 3)],
            'negative' => [YearMonth::of(2015, 3), -2, YearMonth::of(2013, 3)],
        ];
    }

    /**
     * @dataProvider provideForYearMath
     *
     * @param YearMonth $source
     * @param int       $amountToSubtract
     * @param YearMonth $expected
     */
    public function testCanSubtractYears(YearMonth $expected, int $amountToSubtract, YearMonth $source): void
    {
        self::assertHashEquals($expected, $source->minusYears($amountToSubtract));
    }

    /**
     * @dataProvider provideForYearMath
     *
     * @param YearMonth $source
     * @param int       $amountToAdd
     * @param YearMonth $expected
     */
    public function testCanAddYears(YearMonth $source, int $amountToAdd, YearMonth $expected): void
    {
        self::assertHashEquals($expected, $source->plusYears($amountToAdd));
    }

    public function provideForMonthMath(): array
    {
        return [
            'positive' => [YearMonth::of(2015, 3), 2, YearMonth::of(2015, 5)],
            'positive-overflow' => [YearMonth::of(2015, 3), 12, YearMonth::of(2016, 3)],
            'negative' => [YearMonth::of(2015, 3), -2, YearMonth::of(2015, 1)],
            'negative-overlow' => [YearMonth::of(2015, 3), -12, YearMonth::of(2014, 3)],
        ];
    }

    /**
     * @dataProvider provideForMonthMath
     *
     * @param YearMonth $source
     * @param int       $amountToSubtract
     * @param YearMonth $expected
     */
    public function testCanSubtractMonth(YearMonth $expected, int $amountToSubtract, YearMonth $source): void
    {
        self::assertHashEquals($expected, $source->minusMonths($amountToSubtract));
    }

    /**
     * @dataProvider provideForMonthMath
     *
     * @param YearMonth $source
     * @param int       $amountToAdd
     * @param YearMonth $expected
     */
    public function testCanAddMonths(YearMonth $source, int $amountToAdd, YearMonth $expected): void
    {
        self::assertHashEquals($expected, $source->plusMonths($amountToAdd));
    }

    /**
     * @return array<array-key, array{ChronoField, bool}>
     */
    public function provideSupportedFields(): array
    {
        return [
            [ChronoField::Year(), true],
            [ChronoField::MonthOfYear(), true],
            [ChronoField::DayOfMonth(), false],
        ];
    }
}