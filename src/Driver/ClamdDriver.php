<?php
namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Socket\Socket;
use Avasil\ClamAv\Socket\SocketFactory;
use Avasil\ClamAv\Socket\SocketInterface;

/**
 * Class ClamdDriver
 * @package Avasil\ClamAv\Driver
 */
class ClamdDriver extends AbstractDriver
{
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
     * @var Socket
     */
    private $socket;

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
        if (is_dir($path)) {
            $command = 'CONTSCAN';
        } else {
            $command = 'SCAN';
        }

        $this->sendCommand($command . ' ' . $path);

        $result = $this->getResponse();

        return $this->filterScanResult($result);
    }

    /**
     * @inheritdoc
     */
    public function scanBuffer($buffer)
    {
        $this->sendCommand('INSTREAM');

        $this->getSocket()->streamData($buffer);

        $result = $this->getResponse();

        if (false != ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', 'buffer:', $filtered[0]);
        }

        return $filtered;
    }

    /**
     * @param string $command
     * @return int|false
     */
    protected function sendCommand($command)
    {
        return $this->sendRequest(sprintf(static::COMMAND, $command));
    }

    /**
     * @param SocketInterface $socket
     */
    public function setSocket(SocketInterface $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @return SocketInterface
     */
    protected function getSocket()
    {
        if (!$this->socket) {
            if ($this->getOption('socket')) { // socket set in config
                $options = [
                    'socket' => $this->getOption('socket')
                ];
            } elseif ($this->getOption('host')) { // host set in config
                $options = [
                    'host' => $this->getOption('host'),
                    'port' => $this->getOption('port', static::PORT)
                ];
            } else { // use defaults
                $options = [
                    'socket' => $this->getOption('socket', static::SOCKET_PATH),
                    'host' => $this->getOption('host', static::HOST),
                    'port' => $this->getOption('port', static::PORT)
                ];
            }
            $this->socket = SocketFactory::create($options);
        }

        return $this->socket;
    }

    /**
     * @param $data
     * @param int $flags
     * @return false|int
     */
    protected function sendRequest($data, $flags = 0)
    {
        if (false == ($bytes = $this->getSocket()->send($data, $flags))) {
            throw new \RuntimeException('Cannot write to socked'); // FIXME
        }
        return $bytes;
    }

    /**
     * @param int $flags
     * @return string|false
     */
    protected function getResponse($flags = MSG_WAITALL)
    {
        $data = $this->getSocket()->receive($flags);
        $this->getSocket()->close();
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
