<?php

namespace Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearQueueCommand extends \Model\ContainerCommand
{
    protected function configure()
    {
        $this
            ->setName('resque:clear-queue')
            ->setDescription('Clear a BCC queue')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resque = $this->getContainer()->get('resque');

        $queue = $input->getArgument('queue');
        $count=$resque->clearQueue($queue);

        $output->writeln('Cleared queue '.$queue.' - removed '.$count.' entries');

        return 0;
    }
}
