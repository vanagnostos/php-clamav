<?php
namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\MissingDriverException;

/**
 * Class DriverFactory
 * @package Avasil\ClamAv\Driver
 */
class DriverFactory implements DriverFactoryInterface
{
    /**
     * Available drivers
     * @var array
     */
    const DRIVERS = [
        'clamscan' => ClamscanDriver::class,
        'clamd_local' => ClamdDriver::class,
        'clamd_remote' => ClamdRemoteDriver::class,
        'default' => ClamscanDriver::class,
    ];

    /**
     * @inheritdoc
     */
    public function createDriver(array $config)
    {
        if (empty($config['driver'])) {
            throw new MissingDriverException('ClamAV driver required, please check your config.');
        }

        if (!array_key_exists($config['driver'], static::DRIVERS)) {
            throw new MissingDriverException(
                sprintf(
                    'Invalid driver "%s" specified. Available options are: %s',
                    $config['driver'],
                    join(', ', array_keys(static::DRIVERS))
                )
            );
        }

        // TODO validate config, more options...

        $driver = static::DRIVERS[$config['driver']];
        return new $driver($config);
    }
}
