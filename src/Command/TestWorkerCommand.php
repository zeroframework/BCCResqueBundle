<?php

namespace Command;

use Job\ExecuteCommandJob;
use Model\ContainerCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestWorkerCommand extends ContainerCommand {
    protected function configure()
    {
        $this
            ->setName('resque:test')
            ->setDescription('Start a test resque worker')
            ->addOption('simulate-task', 's', InputOption::VALUE_NONE, 'simulate task')
        ;
    }

    /**
     * @return \service\Resque
     */
    public function getResquee()
    {
        return $this->getContainer()->get("resque");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("c moiiiiiiiiiii");

        $executeCommand = new ExecuteCommandJob();

        $command = "resque:test";

        $parameters = array(

            )
        ;

        $executeCommand->setCommand($command, $parameters);

        $this->getResquee()->enqueue($executeCommand);
    }
}

?>