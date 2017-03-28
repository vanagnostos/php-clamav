<?php
namespace Avasil\ClamAv;

use Avasil\ClamAv\Driver\DriverFactory;
use Avasil\ClamAv\Driver\DriverFactoryInterface;
use Avasil\ClamAv\Driver\DriverInterface;
use Avasil\ClamAv\Exception\InvalidTargetException;

class Scanner
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var DriverFactoryInterface
     */
    protected $driverFactory;

    /**
     * @var array
     */
    protected $options = [
        'driver' => 'default'
    ];

    /**
     * Scanner constructor.
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * ping command is used to see whether Clamd is alive or not
     * @return bool
     */
    public function ping()
    {
        return $this->getDriver()->ping();
    }

    /**
     * version is used to receive the version of Clamd
     * @return string
     */
    public function version()
    {
        return $this->getDriver()->version();
    }

    /**
     * scan is used to scan single file.
     * @param $file
     * @return array
     * @throws InvalidTargetException
     */
    public function scan($file)
    {
        if (!is_readable($file)) {
            throw new InvalidTargetException(
                sprintf('%s does not exist or is not readable.')
            );
        }

        $result = [];

        $infected = $this->getDriver()->scan($file);

        foreach ($infected as $line) {
            list($file, $virus) = explode(':', $line);
            $result[$file] = $virus;
        }

        return $result;
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        if (!$this->driver) {
            $this->driver = $this->getDriverFactory()->createDriver(
                $this->options
            );
        }
        return $this->driver;
    }

    /**
     * @param DriverInterface $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return DriverFactoryInterface
     */
    public function getDriverFactory()
    {
        if (!$this->driverFactory) {
            $this->driverFactory = new DriverFactory();
        }
        return $this->driverFactory;
    }

    /**
     * @param DriverFactoryInterface $driverFactory
     */
    public function setDriverFactory($driverFactory)
    {
        $this->driverFactory = $driverFactory;
    }
}
