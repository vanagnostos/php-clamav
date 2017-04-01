<?php
namespace Avasil\ClamAv;

/**
 * Interface ResultInterface
 * @package Avasil\ClamAv
 */
class Result implements ResultInterface
{
    /**
     * @var string
     */
    private $target;

    /**
     * @var array
     */
    private $infected;

    /**
     * Result constructor.
     * @param string $target
     * @param array $infected
     */
    public function __construct($target, array $infected = [])
    {
        $this->target = $target;
        $this->infected = $infected;
    }

    /**
     * @inheritdoc
     */
    public function isClean()
    {
        return !$this->isInfected();
    }

    /**
     * @inheritdoc
     */
    public function isInfected()
    {
        return count($this->infected);
    }

    /**
     * @inheritdoc
     */
    public function getInfected()
    {
        return $this->infected;
    }

    /**
     * @inheritdoc
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $file
     * @param string $virus
     */
    public function addInfected($file, $virus)
    {
        $this->infected[$file] = $virus;
    }

    /**
     * @param array $infected
     */
    public function setInfected($infected)
    {
        $this->infected = $infected;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $str = [];
        foreach ($this->infected as $k => $v) {
            $str[] = $k . ': ' . $v;
        }
        return join(PHP_EOL, $str);
    }
}
