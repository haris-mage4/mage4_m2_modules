<?php

namespace Mage4\RunSql\Console;

use Magento\Framework\App\ResourceConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunSqlCommand extends Command
{
    private $connection;

    public function __construct(ResourceConnection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this->setName('mage4:run-sql')
            ->setDescription('Executes arbitrary SQL directly from the command line.')
            ->addArgument('sql', InputArgument::REQUIRED, 'The SQL statement to execute.')
            ->addOption('force-fetch','f',InputOption::VALUE_NONE,'Forces fetching the result.'
        );
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);

        $sql = $input->getArgument('sql');
        if (empty($sql)) {
            throw new \RuntimeException("Argument 'SQL' is required in order to execute this command correctly.");
        }
        $forceFetch = \boolval($input->getOption('force-fetch'));

        if (stripos($sql, 'select') === 0 || $forceFetch) {
            $this->runSelectQuery($io, $sql);
        } else {
            $this->runStatement($io, $sql);
        }
        return 0;
    }

    /**
     * @param SymfonyStyle $io
     * @param string $sql
     * @return void
     */
    private function runSelectQuery(SymfonyStyle $io, string $sql): void
    {
        $result = $this->connection->getConnection()->fetchAssoc($sql);
        if (empty($result)) {
            $io->success('The query yielded an empty result set.');
            return;
        }
        $io->table(\array_keys(\reset($result)), $result);
    }

    /**
     * @param SymfonyStyle $io
     * @param string $sql
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    private function runStatement(SymfonyStyle $io, string $sql): void
    {
        $io->success(
            \sprintf('%d rows affected.', $this->connection->getConnection()->query($sql)->rowCount())
        );
    }
}
