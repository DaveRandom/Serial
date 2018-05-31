<?php declare(strict_types=1);

namespace DaveRandom\Serial;

use DaveRandom\Enum\Enum;

final class RtsMode extends Enum
{
    public const OFF = 0;
    public const ON = 1;
    public const HANDSHAKE = 2;
    public const TOGGLE = 3;
}
