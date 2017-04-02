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
    const EXECUTABLE = '/usr/bin/clamscan';

    /**
     * @var string
     */
    const COMMAND = '--infected --no-summary --recursive %s';

    /**
     * @var int
     */
    const CLEAN = 0;

    /**
     * @var int
     */
    const INFECTED = 1;

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
        $safe_path = escapeshellarg($path);

        // Reset the values.
        $return = -1;

        $cmd = $this->getExecutable() . ' ' . sprintf($this->getCommand(), $safe_path);

        // Execute the command.
        exec($cmd, $out, $return);

        return $this->parseResults($return, $out);
    }

    /**
     * @inheritdoc
     * @throws \RuntimeException
     */
    public function scanBuffer($buffer)
    {
        $descriptorSpec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that clamscan will read from
            1 => array("pipe", "w"),  // stdout is a pipe that clamscan will write to
        );

        $cmd = $this->getExecutable() . ' ' . sprintf($this->getCommand(), '-');

        $process = @ proc_open($cmd, $descriptorSpec, $pipes);

        if (!is_resource($process)) {
            // FIXME exc
            throw new \RuntimeException('Failed to open a process file pointer');
        }

        // write data to stdin
        fwrite($pipes[0], $buffer);
        fclose($pipes[0]);

        // get response
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        // get return value and close
        $return = proc_close($process);

        return $this->parseResults($return, explode("\n", $out));
    }

    /**
     * @return string
     */
    protected function getExecutable()
    {
        return $this->getOption('executable', static::EXECUTABLE);
    }

    /**
     * @return string
     */
    protected function getCommand()
    {
        return $this->getOption('command', static::COMMAND);
    }

    /**
     * @return int
     */
    protected function getInfected()
    {
        return $this->getOption('infected', static::INFECTED);
    }

    /**
     * @return int
     */
    protected function getClean()
    {
        return $this->getOption('clean', static::CLEAN);
    }

    /**
     * @param int $return
     * @param array $out
     * @return array
     */
    private function parseResults($return, array $out)
    {
        $result = [];
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
}
