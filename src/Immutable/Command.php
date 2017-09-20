<?php

namespace Potherca\Katwizy\Immutable;

class Command
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /*/ const COMMAND_TYPE_COMPOSER = 'composer';/*/// Not yet implemented
    const COMMAND_TYPE_PHP = 'php'; // PHP static call = "Namespace\Class::method"
    const COMMAND_TYPE_SHELL = 'shell'; // <- normal command line calls
    const COMMAND_TYPE_SYMFONY = 'symfony'; // the command after `console`

    const ERROR_UNSUPPORTED_TYPE = 'Type must be one of "%s", "%s" given';

    /** @var string */
    private $callable;
    /** @var string */
    private $arguments;
    /** @var string */
    private $type;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /** @return string */
    public function getCallable()
    {
        return $this->callable;
    }

    /** @return string */
    public function getArguments()
    {
        return $this->arguments;
    }

    /** @return string */
    public function getType()
    {
        return $this->type;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * Command constructor.
     *
     * @param string $type
     * @param string $callable
     * @param array $arguments
     *
     * @throws \InvalidArgumentException
     */
    final public function __construct($type, $callable, array $arguments = [])
    {
        $this->validateType($type);

        $this->callable = $callable;
        $this->arguments = $arguments;
        $this->type = $type;
    }

    /**
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    final public function validateType($type)
    {
        $class = new \ReflectionObject($this);
        $constants = $class->getConstants();

        if ($type !== null && in_array($type, $constants, true) === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    self::ERROR_UNSUPPORTED_TYPE,
                    implode('", "', $constants),
                    $type
                )
            );
        }
    }
}

/*EOF*/
