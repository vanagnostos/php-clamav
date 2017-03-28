<?php
namespace Avasil\ClamAv\Traits;

/**
 * Trait GetOptionTrait
 * @package Avasil\ClamAv\Traits
 */
trait GetOptionTrait
{
    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    protected function getOption($key, $default = null)
    {
        return !empty($this->options[$key]) ? $this->options[$key] : $default;
    }
}
