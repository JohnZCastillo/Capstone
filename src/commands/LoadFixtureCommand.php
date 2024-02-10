<?php

namespace App\commands;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:load-fixtures')]
class LoadFixtureCommand extends Command
{

    private  EntityManager $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $executor = new ORMExecutor($this->entityManager, new ORMPurger());
        $fixture = require __DIR__ . '/../fixtures/FixtureLoader.php';

        $executor->execute($fixture->getFixtures(), append: true);

        echo 'Done';

        return Command::SUCCESS;
    }


}