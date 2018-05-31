<?php declare(strict_types=1);

namespace DaveRandom\Serial\Windows;

use DaveRandom\Enum\Enum;
use DaveRandom\Serial\DeviceConfig;
use DaveRandom\Serial\DtrMode;
use DaveRandom\Serial\Exceptions\Windows\DeviceConfigParseFailedException;
use DaveRandom\Serial\ParityMode;
use DaveRandom\Serial\RtsMode;
use DaveRandom\Serial\StopBits;

final class DeviceConfigParser
{
    private const PARAM_BAUD = 'baud';
    private const PARAM_PARITY = 'parity';
    private const PARAM_DATA_BITS = 'data_bits';
    private const PARAM_STOP_BITS = 'stop_bits';
    private const PARAM_TIMEOUT = 'timeout';
    private const PARAM_XON_XOFF = 'xon_xoff';
    private const PARAM_CTS_HANDSHAKING = 'cts_handshaking';
    private const PARAM_DSR_HANDSHAKING = 'dsr_handshaking';
    private const PARAM_DSR_SENSITIVITY = 'dsr_sensitivity';
    private const PARAM_DTR_CIRCUIT = 'dtr_circuit';
    private const PARAM_RTS_CIRCUIT = 'rts_circuit';

    /**
     * @throws DeviceConfigParseFailedException
     */
    private function getParameterIntValue(array $params, string $param): int
    {
        if (!isset($params[$param])) {
            throw new DeviceConfigParseFailedException(\sprintf(
                "mode command output did not contain parameter '%s'",
                $param
            ));
        }

        if (!\ctype_digit($params[$param])) {
            throw new DeviceConfigParseFailedException(\sprintf(
                "Value '%s' for mode command output parameter '%s' is not a valid integer",
                $params[$param],
                $param
            ));
        }

        return (int)$params[$param];
    }

    /**
     * @throws DeviceConfigParseFailedException
     */
    private function getParameterBoolValue(array $params, string $param): bool
    {
        if (!isset($params[$param])) {
            throw new DeviceConfigParseFailedException(\sprintf(
                "mode command output did not contain parameter '%s'",
                $param
            ));
        }

        if (false === $value = \array_search(\strtolower($params[$param]), ['off', 'on'])) {
            throw new DeviceConfigParseFailedException(\sprintf(
                "Value '%s' for mode command output parameter '%s' is not a valid boolean",
                $params[$param],
                $param
            ));
        }

        return (bool)$value;
    }

    /**
     * @param Enum|string $enumClass
     * @throws DeviceConfigParseFailedException
     */
    private function getParameterEnumValue(array $params, string $param, string $enumClass): int
    {
        if (!isset($params[$param])) {
            throw new DeviceConfigParseFailedException(\sprintf(
                "mode command output did not contain parameter '%s'",
                $param
            ));
        }

        try {
            return $enumClass::parseName($params[$param], true);
        } catch (\InvalidArgumentException $e) {
            throw new DeviceConfigParseFailedException(\sprintf(
                "Value '%s' for mode command output parameter '%s' is not a valid member of the %s enumeration",
                $params[$param],
                $param,
                $enumClass
            ));
        }
    }

    /**
     * @throws DeviceConfigParseFailedException
     */
    private function getStopBitsEnumValue(array $params): int
    {
        if (!isset($params['stop_bits'])) {
            throw new DeviceConfigParseFailedException(\sprintf(
                "mode command output did not contain parameter '%s'",
                self::PARAM_STOP_BITS
            ));
        }

        switch ($params[self::PARAM_STOP_BITS]) {
            case '1': return StopBits::ONE;
            case '1.5': return StopBits::ONE_FIVE;
            case '2': return StopBits::TWO;
        }

        throw new DeviceConfigParseFailedException(\sprintf(
            "Value '%s' for mode command output parameter '%s' is not a valid member of the %s enumeration",
            $params[self::PARAM_STOP_BITS],
            self::PARAM_STOP_BITS,
            StopBits::class
        ));
    }

    /**
     * @return DeviceConfig[]
     * @throws DeviceConfigParseFailedException
     */
    public function parse(string $commandOutput): array
    {
        $result = [];

        foreach (\preg_split('(\r?\n\r?\n)', \trim($commandOutput)) as $deviceOutput) {
            $deviceOutput= \trim($deviceOutput);

            if (!\preg_match('(^status for device com([0-9]+))i', $deviceOutput, $match)) {
                continue;
            }

            $id = (int)$match[1];
            $params = [];

            foreach (\preg_split('(\r?\n)', $deviceOutput) as $line) {
                if (\preg_match('(^\s+(.+):\s+(.+))', $line, $match)) {
                    $params[\preg_replace('([^a-z0-9])i', '_', \strtolower($match[1]))] = $match[2];
                }
            }

            $result[$id] = new DeviceConfig(
                $this->getParameterIntValue($params, self::PARAM_BAUD),
                $this->getParameterEnumValue($params, self::PARAM_PARITY, ParityMode::class),
                $this->getParameterIntValue($params, self::PARAM_DATA_BITS),
                $this->getStopBitsEnumValue($params),
                $this->getParameterBoolValue($params, self::PARAM_TIMEOUT),
                $this->getParameterBoolValue($params, self::PARAM_XON_XOFF),
                $this->getParameterBoolValue($params, self::PARAM_CTS_HANDSHAKING),
                $this->getParameterBoolValue($params, self::PARAM_DSR_HANDSHAKING),
                $this->getParameterBoolValue($params, self::PARAM_DSR_SENSITIVITY),
                $this->getParameterEnumValue($params, self::PARAM_DTR_CIRCUIT, DtrMode::class),
                $this->getParameterEnumValue($params, self::PARAM_RTS_CIRCUIT, RtsMode::class)
            );
        }

        return $result;
    }
}
