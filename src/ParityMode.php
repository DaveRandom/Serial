<?php declare(strict_types=1);

namespace DaveRandom\Serial;

use DaveRandom\Enum\Enum;

final class ParityMode extends Enum
{
    public const NONE  = 0;
    public const EVEN  = 1;
    public const ODD   = 2;
    public const MARK  = 3;
    public const SPACE = 4;
}
