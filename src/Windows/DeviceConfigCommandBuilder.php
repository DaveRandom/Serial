<?php declare(strict_types=1);

namespace DaveRandom\Serial\Windows;

use DaveRandom\Serial\DeviceConfig;
use DaveRandom\Serial\DtrMode;
use DaveRandom\Serial\ParityMode;
use DaveRandom\Serial\RtsMode;
use DaveRandom\Serial\StopBits;

final class DeviceConfigCommandBuilder
{
    private const PARITY_MODES = [
        ParityMode::NONE => 'n',
        ParityMode::EVEN => 'e',
        ParityMode::ODD => 'o',
        ParityMode::MARK => 'm',
        ParityMode::SPACE => 's',
    ];

    private const STOP_BITS = [
        StopBits::ONE => '1',
        StopBits::ONE_FIVE => '1.5',
        StopBits::TWO => '2',
    ];

    private const DTR_MODE = [
        DtrMode::OFF => 'off',
        DtrMode::ON => 'on',
        DtrMode::HANDSHAKE => 'hs',
    ];

    private const RTS_MODE = [
        RtsMode::OFF => 'off',
        RtsMode::ON => 'on',
        RtsMode::HANDSHAKE => 'hs',
        RtsMode::TOGGLE => 'tg',
    ];

    private function getBool(bool $value): string
    {
        return $value ? 'on' : 'off';
    }

    public function buildCommandArgs(int $id, DeviceConfig $config): array
    {
        return [
            "COM{$id}",
            "baud={$config->getBaudRate()}",
            'parity=' . self::PARITY_MODES[$config->getParityMode()],
            "data={$config->getDataBits()}",
            'stop=' . self::STOP_BITS[$config->getStopBits()],
            "to={$this->getBool($config->isTimeoutEnabled())}",
            "xon={$this->getBool($config->isXOnXOffEnabled())}",
            "octs={$this->getBool($config->isCtsHandshakingEnabled())}",
            "odsr={$this->getBool($config->isDsrHandshakingEnabled())}",
            "idsr={$this->getBool($config->isDsrSensitivityEnabled())}",
            'dtr=' . self::DTR_MODE[$config->getDtrMode()],
            'rts=' . self::RTS_MODE[$config->getRtsMode()],
        ];
    }
}
