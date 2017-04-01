<?php
namespace Avasil\ClamAv;

/**
 * Interface ScannerInterface
 * @package Avasil\ClamAv
 */
interface ScannerInterface
{
    /**
     * scan is used to scan file or directory.
     * @param $path
     * @return array
     */
    public function scan($path);

    /**
     * scanBuffer is used to scan in-memory data
     * @param $buffer
     * @return array
     * @internal param $path
     */
    public function scanBuffer($buffer);

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
