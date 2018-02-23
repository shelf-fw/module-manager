<?php

namespace Shelf\ModuleManager\Console;

use Shelf\ModuleManager\Model\ModuleInterface;
use Shelf\ModuleManager\Service\ModuleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModuleListCommand extends Command
{
    /**
     * @var ModuleService
     */
    private $moduleService;

    /**
     * ModuleListCommand constructor.
     * @param ModuleService $moduleService
     * @param null|string $name
     */
    public function __construct(
        ModuleService $moduleService,
        $name = null
    )
    {
        parent::__construct($name);
        $this->moduleService = $moduleService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('module:list')
            ->setDescription('List all Shelf installed modules');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            'Shelf Module List',
            '=================',
            //'',
        ]);

        $moduleCollection = $this->moduleService->getCollection();

        $rows = $moduleCollection->map(function ($module) {
            /** @var ModuleInterface $module */
            return [
                $module->getVendorName(),
                $module->getModuleName(),
                $module->getComposerVersion(),
                $module->getInstallationType(),
            ];
        })->toArray();


        $table = new Table($output);

        $table->setHeaders([
            'Vendor Name',
            'Module Name',
            'Version',
            'Installation Type',
        ]);

        $table->setRows($rows);
        $table->render();
    }
}