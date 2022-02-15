<?php


namespace bin\Command\AppBuild;


use Lynxx\Lynxx;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    private FilesParser $filesParser;
    private ResourcesListManager $resourcesManager;
    private ContainerInterface $container;

    /**
     * Command constructor.
     * @param FilesParser $filesParser
     * @param ContainerInterface $container
     */
    public function __construct(FilesParser $filesParser, ContainerInterface $container, ResourcesListManager $resourcesManager)
    {
        $this->filesParser = $filesParser;
        $this->resourcesManager = $resourcesManager;
        $this->container = $container;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('build:run');
        $this->setDescription('build application for production deployment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = [];
        if ($handle = opendir(__DIR__ . '/../../../app/Controller/')) {
            while (false !== ($file = readdir($handle))) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $templates = array_merge($templates, $this->filesParser->getControllerViews(__DIR__ . '/../../../app/Controller/' . $file));
            }
            closedir($handle);
        }

        $savedList = [];

        try {
            foreach (array_unique($templates) as $template) {
                $output->writeln('view ::: ' . $template);

                $this->resourcesManager->processResources(
                    $template,
                    $this->filesParser->getTemplateResources($template, ResourcesListManager::RES_TYPE_JS),
                    ResourcesListManager::RES_TYPE_JS
                );
                $this->resourcesManager->processResources(
                    $template,
                    $this->filesParser->getTemplateResources($template, ResourcesListManager::RES_TYPE_CSS),
                    ResourcesListManager::RES_TYPE_CSS
                );

                echo print_r($this->resourcesManager->getResourcesList(), true);
            }
        } catch (\Throwable $ex) {
            $output->writeln('ERROR ::: ' . $ex->getMessage());
        }





        return BuildCommand::SUCCESS;
    }

}