<?php

namespace Wabel\CertainAPI\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wabel\CertainAPI\Services\DetectAppointmentsChangingsService;

class DetectAppointmentsChangingCommand extends Command
{

    /**
     * @var DetectAppointmentsChangingsService
     */
    private $detectAppointmentsChangingsService;

    /**
     * DetectAppointmentsChangingCommand constructor.
     * @param DetectAppointmentsChangingsService $detectAppointmentsChangingsService
     * @param string|null $name
     */
    public function __construct(DetectAppointmentsChangingsService $detectAppointmentsChangingsService, string $name = null)
    {
        parent::__construct($name);
        $this->detectAppointmentsChangingsService = $detectAppointmentsChangingsService;
    }

    protected function configure()
    {
        $this
            ->setName('certain:detect-changings')
            ->setDescription('Detect changings of appointments from  Certain Event.')
            ->setHelp(<<<EOT
Request Certain to get appointments and detect changes between to request
EOT
            );
        $this->addArgument('eventCode', InputArgument::REQUIRED, 'Specify the eventCode from Certain');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectAppointmentsChangingsService->runCommandForEvent($input->getArgument('eventCode'), $output);
    }

}
