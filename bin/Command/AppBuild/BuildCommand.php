<?php


namespace bin\Command\AppBuild;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    private FilesParser $filesParser;

    /**
     * Command constructor.
     * @param FilesParser $filesParser
     */
    public function __construct(FilesParser $filesParser)
    {
        $this->filesParser = $filesParser;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('build:run');
        $this->setDescription('build application for production deployment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $viewFiles = [];
        if ($handle = opendir(__DIR__ . '/../../../app/Controller/')) {
            while (false !== ($file = readdir($handle))) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $viewFiles = array_merge($viewFiles, $this->filesParser->getControllerViews(__DIR__ . '/../../../app/Controller/' . $file));
            }
            closedir($handle);
        }



        var_dump(array_unique($viewFiles));





        return BuildCommand::SUCCESS;
    }

}