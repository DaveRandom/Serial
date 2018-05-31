<?php declare(strict_types=1);

namespace DaveRandom\Serial;

use DaveRandom\Enum\Enum;

final class StopBits extends Enum
{
    public const ONE = 1;
    public const TWO = 2;
    public const ONE_FIVE = -1;
}
