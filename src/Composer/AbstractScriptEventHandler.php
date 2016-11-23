<?php

namespace Potherca\Katwizy\Composer;

use Composer\Script\Event;
use Potherca\Katwizy\Command\ImmutableCommand;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Makes it trivial to add commands that are to be called from the Composer Scripts
 */
abstract class AbstractScriptEventHandler
{
    ////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\
    const ERROR_METHOD_NOT_CALLABLE = '<warning>Method "%s" could not be called</warning>';
    const ERROR_CLASS_NOT_LOADABLE = '<warning>Class "%s" could not be auto-loaded</warning>';

    /** @var  ProcessBuilder */
    private $processBuilder;

    //////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * Returns a list of commands to run
     *
     * To only call certain scripts on specific events, get the event name (with
     * `$event->getName()`) and check it against the available events in
     * Composer\Script\ScriptEvents. The most commonly used events are:
     *
     * - ScriptEvents::POST_INSTALL_CMD
     * - ScriptEvents::POST_UPDATE_CMD
     *
     * Command provided by the Symfony `console` command should be marked as
     * `COMMAND_TYPE_SYMFONY`. These will be handled the same as COMMAND_TYPE_SHELL
     *
     * @param Event $event
     *
     * @return ImmutableCommand[]
     *
     * @throws \InvalidArgumentException
     *
     */
    abstract public function getCommands(Event $event);

    /**
     * @return ProcessBuilder
     */
    protected function getProcessBuilder()
    {
        if ($this->processBuilder === null) {
            $this->processBuilder = new ProcessBuilder();
        }
        return $this->processBuilder;
    }

    //////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    final public static function handleEvent(Event $event)
    {
        $self = new static();

        $commands = $self->getCommands($event);

        array_walk(
            $commands,
            function (ImmutableCommand $command) use ($self, $event) {
                $self->executeCommand($event, $command);
            }
        );
    }

    ////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    /**
     * @param Event $event
     * @param ImmutableCommand $command
     *
     * @throws \InvalidArgumentException
     */
    private function executeCommand(Event $event, ImmutableCommand $command)
    {
        switch ($command->getType()) {
            case $command::COMMAND_TYPE_PHP:
                $method = 'executePhpCommand';
                break;

            case $command::COMMAND_TYPE_SHELL:
            case $command::COMMAND_TYPE_SYMFONY:
                $method = 'executeShellCommand';
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf('Command type "%s" is not supported', $command->getType())
                );
        }

        try {
            $this->$method($event, $command);
        } catch (ProcessFailedException $e) {
            $event->getIO()->writeError($e->getMessage());
        }
    }

    protected function executeShellCommand(Event $event, ImmutableCommand $command)
    {
        $callable = $command->getCallable();
        $arguments = $command->getArguments();

        if ($command->getType() === $command::COMMAND_TYPE_SYMFONY) {
            $binDir = $event->getComposer()->getConfig()->get('bin-dir');
            array_unshift($arguments, $callable);
            $callable = sprintf('%s/console', $binDir);
        }

        $processBuilder = $this->getProcessBuilder();
        $process = $processBuilder->setPrefix($callable)
            ->setArguments($arguments)
            ->getProcess()
        ;

        $process->mustRun();

        $event->getIO()->write($process->getOutput());
    }

    protected function executePhpCommand(Event $event, ImmutableCommand $command)
    {
        $result = null;

        $callable = $command->getCallable();

        $className = substr($callable, 0, strpos($callable, '::'));
        $methodName = substr($callable, strpos($callable, '::') + 2);

        if (class_exists($className) === false) {
            $event->getIO()->writeError(sprintf(self::ERROR_CLASS_NOT_LOADABLE, $className));
        } elseif (is_callable($callable) === false) {
            $event->getIO()->writeError(sprintf(self::ERROR_METHOD_NOT_CALLABLE, $callable));
        } else {
            $event->getIO()->write(sprintf('<info>Calling PHP method "%s"</info>', $callable));
            $result = $className::$methodName($event);
        }

        return $result;
    }
}

/*EOF*/
