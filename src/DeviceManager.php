<?php declare(strict_types=1);

namespace DaveRandom\Serial;

use DaveRandom\Serial\Exceptions\CommandExecutionFailedException;
use DaveRandom\Serial\Exceptions\DeviceConfigAssignmentFailedException;
use DaveRandom\Serial\Exceptions\DeviceConfigRetrievalFailedException;
use DaveRandom\Serial\Exceptions\DeviceOpenFailedException;
use DaveRandom\Serial\Exceptions\NotImplementedException;
use DaveRandom\Serial\Windows\DeviceManager as WindowsDeviceManager;

abstract class DeviceManager
{
    /**
     * @throws NotImplementedException
     */
    public static function create(): self
    {
        if (\PHP_OS === 'WINNT') {
            return new WindowsDeviceManager();
        }

        throw new NotImplementedException("Device management is not implemented for this operating system (yet)");
    }

    protected function __construct() { }

    /**
     * @throws CommandExecutionFailedException
     */
    protected function exec(string $command): CommandResult
    {
        $proc = \proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);

        if ($proc === false) {
            throw new CommandExecutionFailedException("Failed to spawn child process for command: {$command}");
        }

        \fclose($pipes[0]);

        $stdOut = \stream_get_contents($pipes[1]);
        $stdErr = \stream_get_contents($pipes[2]);

        \fclose($pipes[1]);
        \fclose($pipes[2]);

        $exitCode = \proc_close($proc);

        return new CommandResult($exitCode, $stdOut, $stdErr);
    }

    /**
     * @return DeviceConfig[]
     * @throws DeviceConfigRetrievalFailedException
     */
    abstract public function getConfigForAllDevices(): array;

    /**
     * @throws DeviceConfigRetrievalFailedException
     */
    abstract public function getConfigForDevice(int $id): DeviceConfig;

    /**
     * @throws DeviceConfigAssignmentFailedException
     */
    abstract public function configureDevice(int $id, DeviceConfig $config): DeviceConfig;

    /**
     * @throws DeviceOpenFailedException
     */
    abstract public function openDevice(int $id, string $mode = 'r+'): Device;
}
