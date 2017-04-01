<?php
namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\InvalidTargetException;

/**
 * Class ClamdRemoteDriver
 * @package Avasil\ClamAv\Driver
 */
class ClamdRemoteDriver extends ClamdDriver
{
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
     */
    public function scan($path)
    {
        if (!is_file($path)) {
            throw new InvalidTargetException('Remote scan of directory is not supported');
        }

        $resource = fopen($path, 'r');

        $this->instreamResource($resource);

        fclose($resource);

        $result = $this->getResponse();

        if (false != ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', $path . ':', $filtered[0]);
        }

        return $filtered;
    }

    /**
     * @param string $data
     * @return false|int
     * @throws InvalidTargetException
     */
    private function instreamData($data)
    {
        if (!is_scalar($data)) {
            throw new InvalidTargetException(
                sprintf('Expected string, received %s', gettype($data))
            );
        }

        $this->sendCommand('INSTREAM');

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
     * @param resource $resource
     * @return false|int
     * @throws InvalidTargetException
     */
    private function instreamResource($resource)
    {
        if (!is_resource($resource)) {
            throw new InvalidTargetException(
                sprintf('Expected resource, received %s', gettype($resource))
            );
        }

        $this->sendCommand('INSTREAM');

        $result = 0;
        while ($chunk = fread($resource, self::BYTES_WRITE)) {
            $result += $this->sendChunk($chunk);
        }

        $result += $this->endStream();

        return $result;
    }

    /**
     * @param $chunk
     * @return false|int
     */
    private function sendChunk($chunk)
    {
        $size = pack('N', strlen($chunk));
        // size packet
        $result = $this->sendRequest($size);
        // data packet
        $result += $this->sendRequest($chunk);
        return $result;
    }

    /**
     * @return false|int
     */
    private function endStream()
    {
        $packet = pack('N', 0);
        return $this->sendRequest($packet);
    }
}
