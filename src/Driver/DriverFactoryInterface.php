<?php
namespace ClamAv\Driver;

/**
 * Interface DriverFactoryInterface
 * @package ClamAv\Driver
 */
interface DriverFactoryInterface
{
    /**
     * @param $config
     * @return DriverInterface
     */
    public function createDriver(array $config);
}
