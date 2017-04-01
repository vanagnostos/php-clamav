<?php
namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\HostOrSocketRequiredException;
use Avasil\ClamAv\Exception\SocketException;

/**
 * Class ClamdDriver
 * @package Avasil\ClamAv\Driver
 */
class ClamdDriver extends AbstractDriver
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
     * @var string
     */
    const HOST = '127.0.0.1';

    /**
     * @var int
     */
    const PORT = 3310;

    /**
     * @var string
     */
    const SOCKET_PATH = '/var/run/clamav/clamd.ctl';

    /**
     * @var string
     */
    const COMMAND = "n%s\n";

    /**
     * @var resource
     */
    protected $socket;

    /**
     * @var bool
     */
    protected $canRead = false;

    /**
     * ClamscanDriver constructor.
     * @param array $options
     * @throws HostOrSocketRequiredException
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (!$this->getOption('socket') && !$this->getOption('host')) {
            throw new HostOrSocketRequiredException(
                'Clamd driver requires host IP address or socket path, please check your config.'
            );
        }

        if ($this->getOption('socket') && !is_readable($this->getOption('socket'))) {
            throw new HostOrSocketRequiredException(
                '%s is not readable.'
            );
        }
    }

    /**
     * ping command is used to see whether Clamd is alive or not
     * @return bool
     */
    public function ping()
    {
        $this->sendCommand('PING');
        return trim($this->getResponse()) === 'PONG';
    }

    /**
     * version is used to receive the version of Clamd
     * @return string
     */
    public function version()
    {
        $this->sendCommand('VERSION');
        return trim($this->getResponse());
    }

    /**
     * @inheritdoc
     */
    public function scan($path)
    {
        $real_path = realpath($path);

        if (is_dir($real_path)) {
            $command = 'CONTSCAN';
        } else {
            $command = 'SCAN';
        }

        $this->sendCommand($command . ' ' . $real_path);

        $result = $this->getResponse();

        return $this->filterScanResult($result);
    }

    /**
     * @param string $command
     * @return int|false
     */
    protected function sendCommand($command)
    {
        return $this->sendRequest(sprintf(self::COMMAND, $command));
    }

    /**
     * @return resource
     */
    protected function getSocket()
    {
        return $this->socket ?: $this->createSocket();
    }

    /**
     * @return void
     */
    protected function closeSocket()
    {
        if (is_resource($this->socket)) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    /**
     * @return resource
     */
    protected function createSocket()
    {
        return $this->socket = $this->getOption('socket') ?
            $this->getUnixSocket() :
            $this->getInetSocket();
    }

    /**
     * @return resource
     * @throws SocketException
     */
    protected function getInetSocket()
    {
        $socket = @socket_create(AF_INET, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new SocketException('', socket_last_error());
        }
        $hasError = @ socket_connect(
            $socket,
            $this->getOption('host', self::HOST),
            $this->getOption('port', self::PORT)
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
    protected function getUnixSocket()
    {
        $socket = @ socket_create(AF_UNIX, SOCK_STREAM, 0);
        if ($socket === false) {
            throw new SocketException('', socket_last_error());
        }
        $hasError = @ socket_connect($socket, $this->getOption('socket', self::SOCKET_PATH));
        if ($hasError === false) {
            $errorCode = socket_last_error();
            $errorMessage = socket_strerror($errorCode);
            throw new SocketException($errorMessage, $errorCode);
        }
        return $socket;
    }

    /**
     * @param $data
     * @param int $flags
     * @return false|int
     */
    protected function sendRequest($data, $flags = 0)
    {
        $socket = $this->getSocket();
        if (false != ($bytes = socket_send($socket, $data, strlen($data), $flags))) {
            $this->canRead = true;
        }
        return $bytes;
    }

    /**
     * @param int $flags
     * @return string|false
     */
    protected function getResponse($flags = MSG_WAITALL)
    {
        $socket = $this->getSocket();

        $data = '';
        while ($bytes = socket_recv($socket, $chunk, self::BYTES_READ, $flags)) {
            $data .= $chunk;
        }

        $this->closeSocket();

        $this->canRead = false;

        return $data;
    }

    /**
     * @param string $result
     * @param string $filter
     * @return array
     */
    protected function filterScanResult($result, $filter = 'FOUND')
    {
        $result = explode("\n", $result);
        $result = array_filter($result);

        $list = [];
        foreach ($result as $line) {
            if (substr($line, -5) === $filter) {
                $list[] = $line;
            }
        }
        return $list;
    }
}
