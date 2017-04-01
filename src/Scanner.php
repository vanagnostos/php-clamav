<?php
namespace Avasil\ClamAv;

use Avasil\ClamAv\Driver\DriverFactory;
use Avasil\ClamAv\Driver\DriverFactoryInterface;
use Avasil\ClamAv\Driver\DriverInterface;
use Avasil\ClamAv\Exception\InvalidTargetException;

class Scanner implements ScannerInterface
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
     * @inheritdoc
     */
    public function ping()
    {
        return $this->getDriver()->ping();
    }

    /**
     * @inheritdoc
     */
    public function version()
    {
        return $this->getDriver()->version();
    }

    /**
     * @inheritdoc
     * @throws InvalidTargetException
     */
    public function scan($path)
    {
        if (!is_readable($path)) {
            throw new InvalidTargetException(
                sprintf('%s does not exist or is not readable.')
            );
        }

        // make sure clamav works with real paths
        $real_path = realpath($path);

        return $this->parseResults(
            $path,
            $this->getDriver()->scan($real_path)
        );
    }

    /**
     * @inheritdoc
     * @internal param $path
     */
    public function scanBuffer($buffer)
    {
        if (!is_scalar($buffer) && (!is_object($buffer) || !method_exists($buffer, '__toString'))) {
            throw new InvalidTargetException(
                sprintf('Expected scalar value, received %s', gettype($buffer))
            );
        }

        return $this->parseResults(
            'buffer',
            $this->getDriver()->scanBuffer($buffer)
        );
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

    /**
     * @param $path
     * @param array $infected
     * @return ResultInterface
     */
    protected function parseResults($path, array $infected)
    {
        $result = new Result($path);

        foreach ($infected as $line) {
            list($file, $virus) = explode(':', $line);
            $result->addInfected($file, $virus);
        }

        return $result;
    }
}
