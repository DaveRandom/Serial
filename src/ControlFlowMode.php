<?php declare(strict_types=1);

namespace DaveRandom\Serial;

use DaveRandom\Enum\Enum;

final class ControlFlowMode extends Enum
{
    public const NONE = 0;
    public const RTS_CTS = 1;
    public const XON_XOFF = 2;
    public const CUSTOM = 3;
}
