<?php
namespace Mage4\IntegrationGoogleCalendar\Console\Command;

use Google\Service\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mage4\IntegrationGoogleCalendar\Helper\AccessClient;

class Integrate extends Command {

    protected $accessClient;

    public function __construct(AccessClient $accessClient)
    {
        $this->accessClient = $accessClient;
        parent::__construct('google:api:integration');
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->accessClient->getClient();
        }catch (Exception $exception){
            $output->writeln('<error>'.$exception->getMessage().'</error>');
        }
    }
    protected function configure()
    {
        $this->setName('google:api:integration');
        $this->setDescription('Google Calendar Api Integration');
        parent::configure();
    }

}
