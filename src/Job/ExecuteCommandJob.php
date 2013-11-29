<?php

namespace Job;

use interfaces\containerAwaireInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;

class ExecuteCommandJob extends \Job implements containerAwaireInterface {

    private $container;

    public function setCommand($command, $input)
    {
        $args["command"] = $command;

        $args["input"] = $input;

        $this->args = $args;
    }

    public function setUp()
    {
        $this->setContainer(\app::getInstance()->getServiceContainer());
    }

    public function run($args)
    {
        $console = $this->getContainer()->get("console");

        $command = $console->find($args["command"]);

        $arrayInput = new ArrayInput(array_merge($args["input"], array(
            "command" => $args["command"]
        )));

        $returnCode = $command->run($arrayInput, new ConsoleOutput());
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }
}