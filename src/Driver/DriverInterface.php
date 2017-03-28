<?php
namespace Avasil\ClamAv\Driver;

/**
 * Interface ClamdDriverInterface
 * @package PHPClamd\Drivers
 */
interface DriverInterface
{
    /**
     * scan is used to scan file or directory.
     * @param $path
     * @return array
     */
    public function scan($path);

    /**
     * ping is used to see whether Clamd is alive or not
     * @return bool
     */
    public function ping();

    /**
     * version is used to receive the version of Clamd
     * @return string
     */
    public function version();
}
