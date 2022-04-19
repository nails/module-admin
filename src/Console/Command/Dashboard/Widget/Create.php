<?php

namespace Nails\Admin\Console\Command\Dashboard\Widget;

use Nails\Admin\Exception\Console\WidgetExistsException;
use Nails\Common\Exception\NailsException;
use Nails\Console\Command\BaseMaker;
use Nails\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends BaseMaker
{
    const RESOURCE_PATH = NAILS_PATH . 'module-admin/resources/console/';
    const WIDGET_PATH   = NAILS_APP_PATH . 'src/Admin/Dashboard/Widget/';

    // --------------------------------------------------------------------------

    /**
     * Configure the command
     */
    protected function configure(): void
    {
        $this
            ->setName('make:admin:dashboard:widget')
            ->setDescription('Creates a new Admin Dashboard Widget')
            ->addArgument(
                'class',
                InputArgument::OPTIONAL,
                'Define the widget\'s class name'
            );
    }

    // --------------------------------------------------------------------------

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

        // --------------------------------------------------------------------------

        try {
            $this->createWidget();
        } catch (\Exception $e) {
            return $this->abort(
                self::EXIT_CODE_FAILURE,
                [$e->getMessage()]
            );
        }

        // --------------------------------------------------------------------------

        //  Cleaning up
        $oOutput->writeln('');
        $oOutput->writeln('<comment>Cleaning up</comment>...');

        // --------------------------------------------------------------------------

        //  And we're done
        $oOutput->writeln('');
        $oOutput->writeln('Complete!');

        return self::EXIT_CODE_SUCCESS;
    }

    // --------------------------------------------------------------------------

    /**
     * Create the Widget
     *
     * @return void
     * @throws \Exception
     */
    private function createWidget(): void
    {
        $aFields  = $this->getArguments();
        $aCreated = [];

        try {

            foreach ($this->parseClassNames($aFields['CLASS']) as $sClass) {

                $aClassSegments = explode('/', $sClass);
                $sClassEnd      = end($aClassSegments);
                array_pop($aClassSegments);
                $sClassPrefix = implode('\\', $aClassSegments);

                $sNamespace = sprintf(
                    'App\\Admin\\Dashboard\\Widget%s',
                    $sClassPrefix ? '\\' . $sClassPrefix : ''
                );

                $sPath = static::WIDGET_PATH . str_replace('/', DIRECTORY_SEPARATOR, $sClass) . '.php';
                $aData = [
                    'PATH'       => $sPath,
                    'NAMESPACE'  => $sNamespace,
                    'CLASS_NAME' => $sClassEnd,
                    'FQN'        => $sNamespace . '\\' . $sClassEnd,
                ];

                $this->oOutput->write('Creating widget <comment>' . $aData['FQN'] . '</comment>... ');

                //  Check for existing widget
                if (file_exists($sPath)) {
                    throw new WidgetExistsException(
                        'Widget "' .  $aData['FQN'] . '" exists already at path "' . $sPath . '"'
                    );
                }

                $this
                    ->createPath(dirname($sPath))
                    ->createFile($sPath, $this->getResource('template/dashboard_widget.php', $aData));

                $aCreated[] = $sPath;
                $this->oOutput->writeln('<info>done</info>');
            }

        } catch (\Exception $e) {
            $this->oOutput->writeln('<error>fail</error>');
            //  Clean up created widgets
            if (!empty($aCreated)) {
                $this->oOutput->writeln('<error>Cleaning up - removing newly created controllers</error>');
                foreach ($aCreated as $sPath) {
                    @unlink($sPath);
                }
            }
            throw new NailsException($e->getMessage());
        }
    }
}
