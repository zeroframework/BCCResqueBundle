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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("c moiiiiiiiiiii");

        if($input->getOption("simulate-task")) {
            $output->writeln("task simulate");

            return;
        }

        $manager = $this->getApplicationManager();

        $command = "resque:test";

        $parameters = array(
                "--simulate-task" => null
            )
        ;

        $manager->getTaskManager()->add($command, $parameters, new \DateInterval("PT10S"));

        $manager->getTaskManager()->add($command, $parameters);
    }
}

?>