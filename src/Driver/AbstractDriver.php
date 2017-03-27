<?php
namespace ClamAv\Driver;

use ClamAv\Traits\GetOptionTrait;

/**
 * Class AbstractDriver
 * @package ClamAv\Driver
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
}
