<?php

namespace Shelf\ModuleManager\Console;

use Shelf\Config\ConfigFactoryAdapter;
use Shelf\Config\ConfigInterface;
use Shelf\Console\Api\ShelfConsoleInterface;
use Shelf\ModuleManager\Helper\Data as DataHelper;
use Shelf\ModuleManager\Model\ModuleInterface;
use Shelf\ModuleManager\Service\ModuleLocalService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Reflection\ClassReflection;
use Zend\Config\Writer\PhpArray;

class ModuleCreateCommandCommand extends BaseCommand
{
    const TEMPLATE_CLASS_REFLECTION = CommandTemplateToReflection::class;
    /**
     * @var ModuleLocalService
     */
    private $moduleLocalService;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * ModuleCreateCommandCommand constructor.
     * @param DataHelper $dataHelper
     * @param ModuleLocalService $moduleLocalService
     * @param Filesystem $fs
     * @param Finder $finder
     * @param null $name
     */
    public function __construct(
        DataHelper $dataHelper,
        ModuleLocalService $moduleLocalService,
        Filesystem $fs,
        Finder $finder,
        $name = null)
    {
        $this->moduleLocalService = $moduleLocalService;
        $this->dataHelper = $dataHelper;
        $this->fs = $fs;
        $this->finder = $finder;
        parent::__construct($dataHelper, $name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('module:command:create')
            ->setDescription('Create a new command for a Shelf Module')
            ->addOption(
                'composer-name',
                'a',
                InputOption::VALUE_REQUIRED,
                'Composer Package Name. Ex: "vendor/module-name"',
                null
            )
            ->addOption(
                'command-name',
                'c',
                InputOption::VALUE_REQUIRED,
                'New Command Name. Ex: "MakeSomething"',
                null
            )
            ->addOption(
                'command-alias',
                null,
                InputOption::VALUE_REQUIRED,
                'New Command Alias. Ex: "namespace:command"'
            );

    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getOption('composer-name');
        $commandName = $input->getOption('command-name');
        $commandAlias = $input->getOption('command-alias');
        $helper = $this->getHelper('question');
        $service = $this->moduleLocalService;
        $localModuleCollection = $service->getCollection();
        $fs = $this->fs;

        if (null === $packageName) {
            $vendorNames = [];
            $moduleNames = [];

            foreach ($localModuleCollection->getValues() as $module) {
                /** @var ModuleInterface $module */
                $vendorNames[] = $module->getVendorName();
                $moduleNames[] = $module->getModuleName();
            }

            $question = $this->getNormalQuestionString('Module Vendor Name', 'VendorName', $vendorNames);
            $vendorName = $helper->ask($input, $output, $question);

            $question = $this->getNormalQuestionString('Module Name', 'ModuleName', $moduleNames);
            $moduleName = $helper->ask($input, $output, $question);

            $packageName = $this->dataHelper->getComposerNameByVendorModuleName($vendorName, $moduleName);
        }

        $module = $service->loadByComposerName($packageName);

        if (! is_object($module)) {
            throw new \RuntimeException('The Module: ' . $packageName . ' does not exist.');
        }

        if (null === $commandName) {
            $question = $this->getQuestionToCamelCase('Command Name', 'MakeSomething');
            $commandName = $helper->ask($input, $output, $question) . 'Command';
        }

        $classFullName = $module->getNameSpace() . $module::MODULE_COMMAND_PATH . '\\' . $commandName;

        if (class_exists($classFullName)) {
            throw new \RuntimeException('This command already exists!');
        }

        if (null == $commandAlias) {
            $question = $this->getNormalQuestionString('Command Alias', 'namespace:command');
            $commandAlias = $helper->ask($input, $output, $question);
        }

        $pathCommands = $module->getLocalPathName() . '/' . $module::MODULE_COMMAND_PATH;

        if (! $fs->exists($pathCommands)) {
            $fs->mkdir($pathCommands);
        }

        $class = ClassGenerator::fromReflection(
            new ClassReflection(self::TEMPLATE_CLASS_REFLECTION)
        );

        $class->setNamespaceName($module->getNameSpace() . $module::MODULE_COMMAND_PATH);
        $class->setName($commandName);
        /*$class
            ->addUse(InputInterface::class)
            ->addUse(OutputInterface::class)
            ->addUse(Command::class);*/

        $methodConfigure = $class->getMethod('configure');
        $methodConfigure->setBody('$this->setName("' . $commandAlias . '");');

        $file = new FileGenerator();
        $file->setClass($class);

        $this->updateModuleSettings($module, $commandName);
        $output->writeln(self::SYMBOL_SUCCESS . ' <info>Module Settings updated!</info>');

        file_put_contents($pathCommands . '/' . $commandName . '.php', $file->generate());

        $output->writeln(self::SYMBOL_SUCCESS . ' <info>Command: ' . $commandName . ' generated!</info>');
    }

    private function updateModuleSettings(ModuleInterface $module, $commandName)
    {
        $moduleConfigPath = $module->getLocalPathName() . '/' . ConfigInterface::CONFG_FOLDER_NAME;

        if (! is_dir($moduleConfigPath)) {
            $this->fs->mkdir($moduleConfigPath);
        }

        $fileSettings = array_filter(array_map(function(SplFileInfo $file) {
            return $file->getPathName();
        }, iterator_to_array($this->finder->files()->in($moduleConfigPath))), function ($file) use ($moduleConfigPath) {
            return $file == $moduleConfigPath . '/console.php';
        });

        $newConfig = new \Zend\Config\Config([
            ShelfConsoleInterface::SHELF_APPLICATION_KEY => [
                ShelfConsoleInterface::COMMANDS_KEY => [
                    $module->getNameSpace() . $module::MODULE_COMMAND_PATH . '\\' . $commandName
                ]
            ]
        ], true);

        $settings = ConfigFactoryAdapter::fromFiles(
            $fileSettings,
            true
        );

        $settings->merge($newConfig);

        $writer = new PhpArray();
        $writer->toFile($moduleConfigPath . DIRECTORY_SEPARATOR . 'console.php', $settings);

    }
}