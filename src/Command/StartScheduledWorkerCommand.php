<?php

namespace Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartScheduledWorkerCommand extends \Model\ContainerCommand
{
    protected function configure()
    {
        $this
            ->setName('resque:scheduledworker-start')
            ->setDescription('Start a bcc scheduled resque worker')
            ->addOption('foreground', 'f', InputOption::VALUE_NONE, 'Should the worker run in foreground')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force creation of a new worker if the PID file exists')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pidFile=APP_DIRECTORY."/cache/bcc_resque_scheduledworker.pid";
        if (file_exists($pidFile) && !$input->getOption('force')) {
            throw new \Exception('PID file exists - use --force to override');
        }

        if (file_exists($pidFile)) {
            unlink($pidFile);
        }

        $env = array(
            'APP_INCLUDE' => $this->getContainer()->get("resque.bootloader"),
            'VVERBOSE'    => 1,
            'RESQUE_PHP' => $this->getContainer()->get('resque.vendor_dir').'/chrisboulton/php-resque/lib/Resque.php',
            'WORKERMODE'  => 1
        );

        $prefix = $this->getContainer()->get('resque.prefix');
        if (!empty($prefix)) {
            $env['PREFIX'] = $this->getContainer()->get('resque.prefix');
        }

        $redisConfiguration = $this->getContainer()->get('resque.redisconfiguration');

        $redisHost = $redisConfiguration["host"];
        $redisPort = $redisConfiguration["port"];
        $redisDatabase = $redisConfiguration["database"];
        if ($redisHost != null && $redisPort != null) {
            $env['REDIS_BACKEND'] = $redisHost.':'.$redisPort;
        }
        if (isset($redisDatabase)) {
            $env['REDIS_BACKEND_DB'] = $redisDatabase;
        }

        $workerCommand = 'php '.$this->getContainer()->get('resque.vendor_dir').'/chrisboulton/php-resque-scheduler/resque-scheduler.php';

        if (!$input->getOption('foreground')) {
            $logFile = APP_DIRECTORY.DIRECTORY_SEPARATOR . "logs". '/resque-scheduler_' . $this->getContainer()->get("kernel")->getName() . '.log';
            $workerCommand = 'nohup ' . $workerCommand . ' > ' . $logFile .' 2>&1 & echo $!';
        }

		// In windows: When you pass an environment to CMD it replaces the old environment
		// That means we create a lot of problems with respect to user accounts and missing vars
		// this is a workaround where we add the vars to the existing environment. 
		if (defined('PHP_WINDOWS_VERSION_BUILD'))
		{
			foreach($env as $key => $value)
			{
				putenv($key."=". $value);
			}
			$env = null;
		}


        $process = new Process($workerCommand, null, $env, null, null);

        $output->writeln(\sprintf('Starting worker <info>%s</info>', $process->getCommandLine()));

        if ($input->getOption('foreground')) {
            $process->run(function ($type, $buffer) use ($output) {
                    $output->write($buffer);
                });
        }
        // else we recompose and display the worker id
        else {
            $process->run();
            $pid = \trim($process->getOutput());
            if (function_exists('gethostname')) {
                $hostname = gethostname();
            } else {
                $hostname = php_uname('n');
            }
            $output->writeln(\sprintf('<info>Worker started</info> %s:%s', $hostname, $pid));
            file_put_contents($pidFile,$pid);
        }
    }
}
