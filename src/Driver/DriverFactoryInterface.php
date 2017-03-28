<?php
namespace Avasil\ClamAv\Driver;

/**
 * Interface DriverFactoryInterface
 * @package Avasil\ClamAv\Driver
 */
interface DriverFactoryInterface
{
    /**
     * @param $config
     * @return DriverInterface
     */
    public function createDriver(array $config);
}
