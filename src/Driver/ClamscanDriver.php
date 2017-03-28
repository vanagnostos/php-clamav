<?php
namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\InvalidTargetException;
use Avasil\ClamAv\Exception\MissingExecutableException;

/**
 * Class ClamscanDriver
 * @package Avasil\ClamAv\Driver
 */
class ClamscanDriver extends AbstractDriver
{
    /**
     * @var string
     */
    const CLAMSCAN_EXECUTABLE = '/usr/bin/clamscan';

    /**
     * @var string
     */
    const CLAMSCAN_COMMAND = '--infected --no-summary --recursive %s';

    /**
     * @var int
     */
    const CLAMSCAN_CLEAN = 0;

    /**
     * @var int
     */
    const CLAMSCAN_INFECTED = 1;

    /**
     * ClamscanDriver constructor.
     * @param array $options
     * @throws MissingExecutableException
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (!is_executable($this->getExecutable())) {
            throw new MissingExecutableException(
                $this->getExecutable() ?
                    sprintf('%s is not valid executable file', $this->getExecutable()) :
                    'Executable required, please check your config.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function ping()
    {
        return !!$this->version();
    }

    /**
     * @inheritdoc
     */
    public function version()
    {
        exec($this->getExecutable() . ' -V', $out, $return);
        if (!$return) {
            return $out[0];
        }
        return '';
    }

    /**
     * @inheritdoc
     * @throws InvalidTargetException
     */
    public function scan($path)
    {
        $real_path = realpath($path);
        $safe_path = escapeshellarg($real_path);

        // Reset the values.
        $return = -1;
        $result = [];

        $cmd = $this->getExecutable() . ' ' . sprintf($this->getCommand(), $safe_path);

        // Execute the command.
        exec($cmd, $out, $return);

        if ($return == $this->getInfected()) {
            foreach ($out as $infected) {
                if (empty($infected)) {
                    break;
                }
                $result[] = $infected;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getExecutable()
    {
        return $this->getOption('executable', self::CLAMSCAN_EXECUTABLE);
    }

    /**
     * @return string
     */
    protected function getCommand()
    {
        return $this->getOption('command', self::CLAMSCAN_COMMAND);
    }

    /**
     * @return int
     */
    protected function getInfected()
    {
        return $this->getOption('infected', self::CLAMSCAN_INFECTED);
    }

    /**
     * @return int
     */
    protected function getClean()
    {
        return $this->getOption('clean', self::CLAMSCAN_CLEAN);
    }
}
