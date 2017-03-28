<?php
namespace Avasil\ClamAv\Exception;

/**
 * Class SocketException
 * @package Clamd\Exception
 */
class SocketException extends \Exception
{
    /**
     * @var int
     */
    protected $errorCode;

    /**
     * SocketException constructor.
     * @param string $message
     * @param int $socketErrorCode
     */
    public function __construct($message, $socketErrorCode)
    {
        $this->errorCode = $socketErrorCode;
        if (!$message) {
            $message = socket_strerror($this->errorCode);
        }
        parent::__construct($message);
    }

    /**
     * Get socket error (returned from 'socket_last_error')
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
