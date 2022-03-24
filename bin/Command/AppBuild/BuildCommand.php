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
    private AssetsListManager $assetsManager;
    private ContainerInterface $container;

    /**
     * Command constructor.
     * @param FilesParser $filesParser
     * @param ContainerInterface $container
     */
    public function __construct(FilesParser $filesParser, ContainerInterface $container, AssetsListManager $assetsManager)
    {
        $this->filesParser = $filesParser;
        $this->assetsManager = $assetsManager;
        $this->container = $container;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('build:run');
        $this->setDescription('build application for production deployment');
    }

    // TODO написать функцию, которая будет всю папку контроллеров пробивать любой вложенности.


    private function getTemplatesFromControllers(string $folder): array
    {
        $templates = [];
        if ($handle = opendir($folder)) {
            while (false !== ($file = readdir($handle))) {
                if ($file === '.' || $file === '..') {
                    continue;
                } else if (strpos($file, ".php") === false) {
                    // if current folder's object belong folder-type (not controller's class file)
                    $templates = array_merge($templates, $this->getTemplatesFromControllers($folder . $file . '/'));
                } else {
                    // if controller's class file
                    $templates = array_merge($templates, $this->filesParser->getControllerViews($folder . $file));
                }
            }
            closedir($handle);
        }
        return $templates;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = $this->getTemplatesFromControllers(__DIR__ . '/../../../app/Controller/');

        $savedList = [];

        try {
            foreach (array_unique($templates) as $template) {
                $output->writeln('view ::: ' . $template);

                $this->assetsManager->resolveTemplateAssets(
                    $template,
                    $this->filesParser->getTemplateAssets($template, AssetsListManager::RES_TYPE_JS),
                    AssetsListManager::RES_TYPE_JS
                );
                $this->assetsManager->resolveTemplateAssets(
                    $template,
                    $this->filesParser->getTemplateAssets($template, AssetsListManager::RES_TYPE_CSS),
                    AssetsListManager::RES_TYPE_CSS
                );

                // Lynxx::debugPrint($this->assetsManager->getAssetsList());
            }

            /** remove temporary keys (modifiedTime) from assets list */
            $this->assetsManager->resolveAssetsList();

        } catch (\Throwable $ex) {
            $output->writeln('ERROR ::: ' . $ex->getMessage());
        }





        return BuildCommand::SUCCESS;
    }

}