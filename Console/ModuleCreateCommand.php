<?php

namespace Shelf\ModuleManager\Console;

use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryInterface;
use Shelf\ModuleManager\Helper\Composer as ComposerHelper;
use Shelf\ModuleManager\Helper\Data as DataHelper;
use Shelf\ModuleManager\Model\Module;
use Shelf\ModuleManager\Model\ModuleInterface;
use Shelf\ModuleManager\Service\ModuleCreateService;
use Shelf\ModuleManager\Service\ModuleService;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ModuleCreateCommand extends BaseCommand
{
    /**
     * @var ModuleService
     */
    private $moduleService;

    /**
     * @var dataHelper
     */
    private $dataHelper;

    /**
     * @var ComposerHelper
     */
    private $composerHelper;

    /**
     * ModuleCreateCommand constructor.
     * @param ModuleService $moduleService
     * @param dataHelper $dataHelper
     * @param ComposerHelper $composerHelper
     * @param null $name
     */
    public function __construct(
        ModuleService $moduleService,
        DataHelper $dataHelper,
        ComposerHelper $composerHelper,
        $name = null
    )
    {
        $this->moduleService = $moduleService;
        parent::__construct(
            $dataHelper,
            $name
        );
        $this->dataHelper = $dataHelper;
        $this->composerHelper = $composerHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('module:create')
            ->setDescription('Create a new Shelf Module')
            ->addOption(
                'ignore-online-check',
                'i',
                InputOption::VALUE_NONE,
                'Ignores the check in Composer repositories registered in this project (ex: packagist.org)'
            );

    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $this->moduleService;
        $helper = $this->getHelper('question');

        // Question Vendor Name
        $localVendorFolders = $service
            ->getModuleLocalService()
            ->getLocalVendorFolders();
        $question = $this->getQuestionToCamelCase('Vendor Name', 'Shelf', $localVendorFolders);
        $vendorName = $helper->ask($input, $output, $question);

        // Question Module Name
        $question = $this->getQuestionToCamelCase('Module Name', 'Module');
        $moduleName = $helper->ask($input, $output, $question);

        $composerName = $this->dataHelper->getComposerNameByVendorModuleName($vendorName, $moduleName);

        $output->writeln(
            '<info>Your new module will have Composer name: </info>'
            . '<fg=blue>' . $composerName . '</>'
        );
        $output->writeln('');

        $output->writeln('<info>Please wait while we check if there is no module with this same namespace and name:</info>');
        $this->isModuleExistsInLocalContext($composerName, $output);

        if (false === $input->getOption('ignore-online-check')) {
            $this->isModuleExistsInRepositoriesContexts($composerName, $output);
        }

        $localModulesCollection = $service->getModuleLocalService()->getCollection();

        $output->writeln('');

        $authorNames = [];
        $authorEmails = [];

        foreach ($localModulesCollection->getValues() as $module) {
            /** @var ModuleInterface $module */
            $authors = $module->getComposerAuthors();

            if (is_array($authors) && 0 < count($authors)) {
                if (isset($authors['name']) || isset($authors['email'])) {
                    $authorNames[] = $authors['name'];
                    $authorEmails[] = $authors['email'];
                } else {
                    foreach ($module->getComposerAuthors() as $author) {
                        if (! isset($author['name']) && ! isset($author['email'])) {
                            $authorNames[] = $author['name'];
                            $authorEmails[] = $author['email'];
                        }
                    }
                }
            }
        }

        $question = $this->getNormalQuestionString('Author Name', 'Your Name', $authorNames);
        $authorName = $helper->ask($input, $output, $question);


        $question = $this->getNormalQuestionString('Author E-mail', 'your@email.com', $authorEmails);
        $question->setValidator(function ($answer) {
            if (! filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid E-mail');
            }

            return $answer;
        });

        $authorEmail = $helper->ask($input, $output, $question);

        $newModule = new Module();
        $newModule
            ->setComposerName($composerName)
            ->setComposerAuthors([
                [
                    'name' => $authorName,
                    'email' => $authorEmail
                ]
            ]);

        $this->createModuleBasicStructure($input, $output, $newModule);
        $this->createModuleComposerFile($input, $output, $newModule);
        $this->composerDumpAutoload($input, $output);
    }

    /**
     * @param $composerName
     * @param OutputInterface $output
     */
    private function isModuleExistsInLocalContext($composerName, OutputInterface $output)
    {
        $progress = new ProgressBar($output, 100);
        $progress->setFormat('%message% - [%bar%] %result%');
        $progress->setProgressCharacter("☕");
        $progress->setMessage(
            '<comment> - Searching for modules under development in the local environment or already installed via Composer</comment>'
        );
        $progress->setMessage('', 'result');
        $progress->start();

        $localModule = $this->moduleService
            ->loadByComposerName($composerName);

        if (is_object($localModule)) {
            $progress->setMessage(self::SYMBOL_ERROR, 'result');
            $progress->advance(100);
            throw new \RuntimeException(
                'The Composer Module already exists in Developer Local Context.'
                . ' Change the your Vendor Name or Module Name'
            );
        }

        for ($i = 0; $i <= 100; $i++) {
            $progress->advance();
            usleep(5000);
        }

        $progress->setMessage(self::SYMBOL_SUCCESS . PHP_EOL, 'result');
        $progress->finish();

    }

    /**
     * @param $composerName
     * @param OutputInterface $output
     */
    private function isModuleExistsInRepositoriesContexts($composerName, OutputInterface $output)
    {
        $progress = new ProgressBar($output, 100);
        $progress->setFormat('%message% - [%bar%] %result%');
        $progress->setProgressCharacter("☕");
        $progress->setEmptyBarCharacter('-');
        $progress->setMessage('<comment> - Searching for Composer packages in registered repositories</comment>');
        $progress->setMessage('', 'result');
        $progress->start();

        for ($i = 0; $i <= 20; $i++) {
            $progress->advance();
        }

        $composer = $this->composerHelper->getComposer();
        $repos = new CompositeRepository($composer->getRepositoryManager()->getRepositories());

        $result = $repos->search($composerName, RepositoryInterface::SEARCH_NAME, null);

        for ($i = 0; $i <= 100; $i++) {
            $progress->advance();
            usleep(20000);
        }

        if (is_array($result) && 0 < count($result)) {
                $progress->setMessage(self::SYMBOL_ERROR, 'result');
                $progress->advance(100);
                throw new \RuntimeException(
                    'The Composer package already exists and is registered in a Composer repository.'
                    . ' Change the your Vendor Name or Module Name'
                );
        }

        $progress->setMessage(self::SYMBOL_SUCCESS . PHP_EOL, 'result');
        $progress->finish();
    }

    private function createModuleBasicStructure(InputInterface $input, OutputInterface $output, ModuleInterface $module)
    {
        $output->writeln('');
        $output->writeln(self::SYMBOL_SUCCESS . ' <info>Creating the Basic Module Structure</info> ');

        $fileSystem = new Filesystem();
        $fileSystem->mkdir(
            $module->getLocalPathName()
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ModuleInterface $module
     */
    private function createModuleComposerFile(InputInterface $input, OutputInterface $output, ModuleInterface $module)
    {
        $output->writeln('');
        $output->writeln(self::SYMBOL_SUCCESS . ' <info>Creating the Composer File</info> ');
        $fileSystem = new Filesystem();

        $composerContent = [
            'name' => $module->getComposerName(),
            'type' => $module::MODULE_COMPOSER_TYPE,
            'authors' => $module->getComposerAuthors(),
            'require' => new \StdClass(),
            'require-dev' => new \StdClass(),
            'autoload' => [
                'psr-4' => [
                     $module->getNameSpace() => ''
                ]
            ]
        ];

        $fileSystem->dumpFile(
            $module->getLocalPathName() . '/composer.json',
            json_encode($composerContent, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function composerDumpAutoload(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln(self::SYMBOL_SUCCESS . ' <info>Composer dump-autoload</info> ');

        $composer = $this->composerHelper->getComposer(true);

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'dump-autoload', $input, $output);
        $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);

        $installationManager = $composer->getInstallationManager();
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        $package = $composer->getPackage();
        $config = $composer->getConfig();

        $generator = $composer->getAutoloadGenerator();
        $generator->setRunScripts(true);
        $generator->dump($config, $localRepo, $package, $installationManager, 'composer');
    }

}