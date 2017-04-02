<?php
namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\RuntimeException;

/**
 * Class ClamdRemoteDriver
 * @package Avasil\ClamAv\Driver
 */
class ClamdRemoteDriver extends ClamdDriver
{
    /**
     * @var string
     */
    const SOCKET_PATH = '';

    /**
     * ClamdRemoteDriver constructor.
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        unset($options['socket']);
        parent::__construct($options);
    }

    /**
     * @inheritdoc
     * @throws RuntimeException
     */
    public function scan($path)
    {
        if (!is_file($path)) {
            throw new RuntimeException('Remote scan of directory is not supported');
        }

        $this->sendCommand('INSTREAM');

        $resource = fopen($path, 'r');

        $this->getSocket()->streamResource($resource);

        fclose($resource);

        $result = $this->getResponse();

        if (false != ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', $path . ':', $filtered[0]);
        }

        return $filtered;
    }
}
