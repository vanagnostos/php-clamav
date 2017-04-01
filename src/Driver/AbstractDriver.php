<?php
namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Traits\GetOptionTrait;

/**
 * Class AbstractDriver
 * @package Avasil\ClamAv\Driver
 */
abstract class AbstractDriver implements DriverInterface
{
    use GetOptionTrait;

    /**
     * @var array
     */
    protected $options;

    /**
     * ClamscanDriver constructor.
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function scanBuffer($buffer)
    {
        // TODO
    }
}
