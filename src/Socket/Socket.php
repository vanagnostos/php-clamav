<?php
namespace Avasil\ClamAv\Socket;

use Avasil\ClamAv\Exception\RuntimeException;
use Avasil\ClamAv\Exception\SocketException;

/**
 * Class Socket
 * @package Avasil\ClamAv\Socket
 */
class Socket implements SocketInterface
{
    /**
     * @var int
     */
    const BYTES_READ = 8192;

    /**
     * @var int
     */
    const BYTES_WRITE = 8192;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $path;

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @param int $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return void
     */
    public function close()
    {
        if (is_resource($this->socket)) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    /**
     * @param $data
     * @param int $flags
     * @return false|int
     */
    public function send($data, $flags = 0)
    {
        $this->reconnect();

        return socket_send($this->socket, $data, strlen($data), $flags);
    }

    /**
     * @param $resource
     * @return false|int
     */
    public function streamResource($resource)
    {
        $this->reconnect();

        $result = 0;
        while ($chunk = fread($resource, self::BYTES_WRITE)) {
            $result += $this->sendChunk($chunk);
        }

        $result += $this->endStream();

        return $result;
    }

    /**
     * @param $data
     * @return false|int
     */
    public function streamData($data)
    {
        $this->reconnect();

        $result = 0;
        $left = $data;
        while (strlen($left) > 0) {
            $chunk = substr($left, 0, self::BYTES_WRITE);
            $left = substr($left, self::BYTES_WRITE);
            $result += $this->sendChunk($chunk);
        }

        $result += $this->endStream();

        return $result;
    }

    /**
     * @param int $flags
     * @return string|false
     * @throws RuntimeException
     */
    public function receive($flags = MSG_WAITALL)
    {
        // $this->reconnect();
        if (!is_resource($this->socket)) {
            throw new RuntimeException('Socket is currently closed');
        }

        $data = '';
        while ($bytes = socket_recv($this->socket, $chunk, self::BYTES_READ, $flags)) {
            $data .= $chunk;
        }

        return $data;
    }

    /**
     * @param $chunk
     * @return false|int
     */
    private function sendChunk($chunk)
    {
        $size = pack('N', strlen($chunk));
        // size packet
        $result = $this->send($size);
        // data packet
        $result += $this->send($chunk);
        return $result;
    }

    /**
     * @return false|int
     */
    private function endStream()
    {
        $packet = pack('N', 0);
        return $this->send($packet);
    }

    /**
     * @return void
     */
    private function connect()
    {
        $this->socket = $this->path ?
            $this->getUnixSocket() :
            $this->getInetSocket();
    }

    /**
     * @return resource
     * @throws SocketException
     */
    private function getInetSocket()
    {
        $socket = @socket_create(AF_INET, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new SocketException('', socket_last_error());
        }
        $hasError = @ socket_connect(
            $socket,
            $this->host,
            $this->port
        );
        if ($hasError === false) {
            throw new SocketException('', socket_last_error());
        }
        return $socket;
    }

    /**
     * @return resource
     * @throws SocketException
     */
    private function getUnixSocket()
    {
        $socket = @ socket_create(AF_UNIX, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new SocketException('', socket_last_error());
        }
        $hasError = @ socket_connect($socket, $this->path);
        if ($hasError === false) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            throw new SocketException($errorMessage, $errorCode);
        }
        return $socket;
    }

    /**
     * @return  void
     */
    private function reconnect()
    {
        if (!is_resource($this->socket)) {
            $this->connect();
        }
    }
}
