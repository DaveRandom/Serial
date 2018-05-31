# Serial

```php
<?php

use DaveRandom\Serial\DeviceManager;
use DaveRandom\Serial\ParityMode;
use DaveRandom\Serial\ControlFlowMode;

// Use COM1
const COM_PORT = 1;

// Create the correct device manager for the current platform (only windows supported so far)
$manager = DeviceManager::create();

// Get the current config for COM1
$config = $manager->getConfigForDevice(COM_PORT);
var_dump($config);

// Modify some parameters and set the config
$config
    ->setBaudRate(9600)
    ->setParityMode(ParityMode::NONE)
    ->setDataBits(8)
    ->setStopBits(1)
    ->setControlFlowMode(ControlFlowMode::NONE)
;
$manager->configureDevice(COM_PORT, $config);

// Open the device
$device = $manager->openDevice(COM_PORT);

// Send a message to the device
$device->write('D');

// Read some data from the device
var_dump($device->read());
```
