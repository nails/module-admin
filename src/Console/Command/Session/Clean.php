<?php

namespace Nails\Admin\Console\Command\Session;

use Nails\Admin\Housekeeping\Sessions;
use Nails\Components;
use Nails\Console\Command\Base;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated Use housekeeping:run --routine=Nails\Admin\Housekeeping\Sessions
 */
class Clean extends Base
{
    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('admin:session:clean')
            ->setDescription('[DEPRECATED] Cleans old admin sessions')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Log what would be deleted without deleting'
            );
    }

    /**
     * Executes the app
     *
     * @param InputInterface  $oInput  The Input Interface provided by Symfony
     * @param OutputInterface $oOutput The Output Interface provided by Symfony
     *
     * @return int
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput): int
    {
        parent::execute($oInput, $oOutput);

        $this->banner('Admin Session: Clean (deprecated)');

        if (!Components::exists('nails/module-housekeeping')) {
            $oOutput->writeln('<error>This command now requires nails/module-housekeeping.</error>');
            $oOutput->writeln('Install it with <comment>composer require nails/module-housekeeping</comment>');
            $oOutput->writeln('then run <comment>nails housekeeping:run --routine=' . Sessions::class . '</comment>');

            return static::EXIT_CODE_FAILURE;
        }

        /** @var \Nails\Housekeeping\Service\Orchestrator $oOrchestrator */
        $oOrchestrator = Factory::service('Orchestrator', 'nails/module-housekeeping');
        $oResult       = $oOrchestrator->runRoutine(
            Sessions::class,
            (bool) $oInput->getOption('dry-run'),
            true,
            $oOutput
        );

        return $oResult->isSuccess() ? static::EXIT_CODE_SUCCESS : static::EXIT_CODE_FAILURE;
    }
}
