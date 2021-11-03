<?php

namespace Bluesome\Parameters;

use Nette;
use Nette\InvalidArgumentException;

class Provider
{

    use Nette\SmartObject;

    const MODE_DEVELOPMENT = 'development';

    const MODE_STAGING = 'staging';

    const MODE_PRODUCTION = 'production';

    /** @var [] */
    private $parameters = [];

    /**
     * @param []
     *
     * @throws InvalidArgumentException
     */
    public function __construct($parameters)
    {
        $this->parameters = Nette\Utils\ArrayHash::from($parameters);

        if (!isset($this->parameters->mode)) {
            throw new InvalidArgumentException("Required parameter 'mode' is missing.");
        }

        $allowedModes = [static::MODE_DEVELOPMENT, static::MODE_PRODUCTION, static::MODE_STAGING];
        if (!in_array($this->parameters->mode, $allowedModes)) {
            throw new InvalidArgumentException("Parameter 'mode' contains invalid value. Expected one of '" . implode("', '", $allowedModes) . "'.");
        }
    }


    /**
     * @param $name
     *
     * @return mixed
     */
    public function &__get($name)
    {
        if (!isset($this->parameters->$name)) {
            throw new InvalidArgumentException("Missing parameter '$name'.");
        }

        return $this->parameters->$name;
    }


    /**
     * @param name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * @return bool
     */
    public function isDevelopment()
    {
        return $this->parameters->mode == static::MODE_DEVELOPMENT;
    }

    /**
     * @return bool
     */
    public function isStaging()
    {
        return $this->parameters->mode == static::MODE_STAGING;
    }

    /**
     * @return bool
     */
    public function isProduction()
    {
        return $this->parameters->mode == static::MODE_PRODUCTION;
    }

}