<?php

declare(strict_types=1);

namespace ParTest\Time\Unit;

use Par\Core\PHPUnit\HashableAssertions;
use Par\Time\DayOfWeek;
use Par\Time\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DayOfWeekTest extends TestCase
{
    use HashableAssertions;

    public function itWillReturnValue(): void
    {
        self::assertSame(2, DayOfWeek::Tuesday()->value());
    }

    /**
     * @test
     */
    public function itCanBeCreatedFromValue(): void
    {
        $expected = DayOfWeek::Thursday();

        self::assertHashEquals($expected, DayOfWeek::of(4));
    }

    /**
     * @test
     */
    public function itWillThrowInvalidArgumentExceptionWhenCreateFromOutOfRangeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DayOfWeek::of(8);
    }

    public function provideNative(): array
    {
        return [
            \DateTime::class => [
                DayOfWeek::Thursday(),
                \DateTime::createFromFormat('Y-m-d', '2021-01-14'),
            ],
            \DateTimeImmutable::class => [
                DayOfWeek::Wednesday(),
                \DateTimeImmutable::createFromFormat('Y-m-d', '2021-01-20'),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider provideNative
     *
     * @param DayOfWeek          $expected
     * @param \DateTimeInterface $native
     *
     * @return void
     */
    public function itCanBeCreatedFromNativeDateTime(DayOfWeek $expected, \DateTimeInterface $native): void
    {
        self::assertSame($expected, DayOfWeek::fromNative($native));
    }

    /**
     * @test
     */
    public function itWillContainAllDaysOfWeek(): void
    {
        self::assertSame(
            [
                DayOfWeek::Monday(),
                DayOfWeek::Tuesday(),
                DayOfWeek::Wednesday(),
                DayOfWeek::Thursday(),
                DayOfWeek::Friday(),
                DayOfWeek::Saturday(),
                DayOfWeek::Sunday(),
            ],
            DayOfWeek::values()
        );
    }

}
