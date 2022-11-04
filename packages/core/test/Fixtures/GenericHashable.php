<?php

declare(strict_types=1);

namespace Par\CoreTest\Fixtures;

use Par\Core\Hashable;

/**
 * @internal
 * @psalm-immutable
 */
final class GenericHashable implements Hashable
{
    public function __construct(private int|string|bool|null|float $value)
    {
    }

    /**
     * @inheritDoc
     */
    public function equals(mixed $obj): bool
    {
        if ($obj instanceof self) {
            return $this->value === $obj->value;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function hash(): int|string|bool|null|float
    {
        return $this->value;
    }
}
