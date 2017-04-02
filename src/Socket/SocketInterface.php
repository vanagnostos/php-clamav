<?php
namespace Avasil\ClamAv\Socket;

use Avasil\ClamAv\Exception\SocketException;

/**
 * Interface SocketInterface
 * @package Avasil\ClamAv\Socket
 */
interface SocketInterface
{
    /**
     * @return void
     */
    public function close();

    /**
     * @param $data
     * @param int $flags
     * @return false|int
     */
    public function send($data, $flags = 0);

    /**
     * @param $resource
     * @return false|int
     */
    public function streamResource($resource);

    /**
     * @param $data
     * @return false|int
     */
    public function streamData($data);

    /**
     * @param int $flags
     * @return string|false
     */
    public function receive($flags = MSG_WAITALL);
}
