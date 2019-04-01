<?php

namespace Shelf\ModuleManager\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Output\OutputInterface;

class CommandTemplateToReflection extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        // @todo implement configure()
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param Input $input
     * @param OutputInterface $output
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(Input $input, OutputInterface $output)
    {
        // @todo implement execute()
        $output->writeln('@todo implement this Command.');
    }
}