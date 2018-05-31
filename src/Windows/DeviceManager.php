<?php declare(strict_types=1);

namespace DaveRandom\Serial\Windows;

use DaveRandom\Serial\Device;
use DaveRandom\Serial\DeviceConfig;
use DaveRandom\Serial\Exceptions\CommandExecutionFailedException;
use DaveRandom\Serial\Exceptions\DeviceConfigAssignmentFailedException;
use DaveRandom\Serial\Exceptions\DeviceConfigRetrievalFailedException;
use DaveRandom\Serial\Exceptions\DeviceOpenFailedException;
use DaveRandom\Serial\Exceptions\Windows\DeviceConfigParseFailedException;

final class DeviceManager extends \DaveRandom\Serial\DeviceManager
{
    /**
     * @return DeviceConfig[]
     * @throws DeviceConfigParseFailedException
     */
    private function parseDeviceConfigOutput(string $output): array
    {
        static $parser;
        return ($parser ?? $parser = new DeviceConfigParser)->parse($output);
    }

    private function buildDeviceConfigCommandArgs(int $id, DeviceConfig $config): array
    {
        static $builder;
        return ($builder ?? $builder = new DeviceConfigCommandBuilder)->buildCommandArgs($id, $config);
    }

    /**
     * @throws CommandExecutionFailedException
     */
    private function executeModeCommand(string ...$args): string
    {
        $command = \trim('mode ' . \implode(' ', $args));
        $result = $this->exec($command);

        if ($result->getExitCode() !== 0) {
            throw new CommandExecutionFailedException(\sprintf(
                "mode command terminated with non-zero exit code: %d: %s",
                $result->getExitCode(),
                $result->getStdErr() ?? $result->getStdOut()
            ));
        }

        return $result->getStdOut();
    }

    /**
     * @return DeviceConfig[]
     * @throws DeviceConfigRetrievalFailedException
     */
    public function getConfigForAllDevices(): array
    {
        try {
            $result = $this->executeModeCommand();
        } catch (CommandExecutionFailedException $e) {
            throw new DeviceConfigRetrievalFailedException($e);
        }

        return $this->parseDeviceConfigOutput($result);
    }

    /**
     * @throws DeviceConfigRetrievalFailedException
     */
    public function getConfigForDevice(int $id): DeviceConfig
    {
        try {
            $result = $this->executeModeCommand("COM{$id}");
        } catch (CommandExecutionFailedException $e) {
            throw new DeviceConfigRetrievalFailedException($e);
        }

        $devices = $this->parseDeviceConfigOutput($result);

        if (!isset($devices[$id])) {
            throw new DeviceConfigRetrievalFailedException(
                "mode command succeeded but did not return config info for device #{$id}"
            );
        }

        return $devices[$id];
    }

    /**
     * @throws DeviceConfigAssignmentFailedException
     */
    public function configureDevice(int $id, DeviceConfig $config): DeviceConfig
    {
        $args = $this->buildDeviceConfigCommandArgs($id, $config);

        try {
            $result = $this->executeModeCommand(...$args);
        } catch (CommandExecutionFailedException $e) {
            throw new DeviceConfigAssignmentFailedException($e);
        }

        try {
            $devices = $this->parseDeviceConfigOutput($result);
        } catch (DeviceConfigRetrievalFailedException $e) {
            throw new DeviceConfigAssignmentFailedException($e);
        }

        if (!isset($devices[$id])) {
            throw new DeviceConfigAssignmentFailedException(
                "mode command succeeded but did not return config info for device #{$id}"
            );
        }

        return $devices[$id];
    }

    /**
     * @throws DeviceOpenFailedException
     */
    public function openDevice(int $id, string $mode = 'r+b'): Device
    {
        $path = "\\\\.\\COM{$id}";

        $fp = \DaveRandom\Serial\capture_errors(function() use($path, $mode) {
            return \fopen($path, $mode);
        }, $errNo, $errStr);

        if ($errNo !== null) {
            throw new DeviceOpenFailedException($errStr, $errNo);
        }

        if ($fp === false) {
            throw new DeviceOpenFailedException('Unknown error');
        }

        return new Device($id, $fp);
    }
}
