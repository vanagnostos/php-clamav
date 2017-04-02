<?php
namespace Avasil\ClamAv\Socket;

use Avasil\ClamAv\Exception\ConfigurationException;

/**
 * Class SocketFactory
 * @package Avasil\ClamAv\Socket
 */
class SocketFactory
{
    /**
     * Create socket
     * @param $options
     * @return SocketInterface
     * @throws ConfigurationException
     */
    public static function create($options)
    {
        if (empty($options['socket']) && empty($options['host'])) {
            throw new ConfigurationException(
                'Socket requires host IP address or socket path, please check your config.'
            );
        }

        $instance = new Socket();
        if (!empty($options['host'])) {
            $instance->setHost($options['host']);
            $instance->setPort($options['port']);
        } else {
            if (!is_readable($options['socket'])) {
                throw new ConfigurationException(
                    sprintf('Socket "%s" does not exist or is not readable.', $options['socket'])
                );
            }
            $instance->setPath($options['socket']);
        }
        return $instance;
    }
}
