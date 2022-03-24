<?php

namespace tests\bin\Command\AppBuild;

use bin\Command\AppBuild\AssetsListManager;
use bin\Command\AppBuild\FilesParser;
use Lynxx\Container\Container;
use PHPUnit\Framework\TestCase;

class AssetsComplexTest extends TestCase
{
    private FilesParser $fileParser;
    private AssetsListManager $assetsManager;
    private Container $container;
    private $fixtTemplateA;



    public static function setUpBeforeClass(): void
    {
        file_put_contents(__DIR__ . '/../../../../web/js/fixture_a.js', '');
        file_put_contents(__DIR__ . '/../../../../web/js/fixture_b.js', '');
        file_put_contents(__DIR__ . '/../../../../web/js/fixture_c.js', '');
        file_put_contents(__DIR__ . '/../../../../web/js/fixture_e.js', '');
        file_put_contents(__DIR__ . '/../../../../web/js/fixture_d.js', '');
        file_put_contents(__DIR__ . '/../../../../web/css/a.css', '');
        file_put_contents(__DIR__ . '/../../../../web/css/b.css', '');
        copy(__DIR__ . '/fixtureTemplateA.php', __DIR__ . '/../../../../app/templates/fixtureTemplateA.php');
    }


    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        unlink(__DIR__ . '/../../../../app/templates/fixtureTemplateA.php');
        unlink(__DIR__ . '/../../../../web/js/fixture_a.js');
        unlink(__DIR__ . '/../../../../web/js/fixture_b.js');
        unlink(__DIR__ . '/../../../../web/js/fixture_c.js');
        unlink(__DIR__ . '/../../../../web/js/fixture_d.js');
        unlink(__DIR__ . '/../../../../web/js/fixture_e.js');
        unlink(__DIR__ . '/../../../../web/css/a.css');
        unlink(__DIR__ . '/../../../../web/css/b.css');

        $container = new Container();
        $assetsManager = $container->get(AssetsListManager::class);

        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_b.js', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_c.js', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_d.js', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/a.css', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/b.css', AssetsListManager::RES_TYPE_CSS);

        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_b.js', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_c.js', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_d.js', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/a.css', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/b.css', AssetsListManager::RES_TYPE_JS);

        $assetsManager->getAssetsList()->removeTemplate('fixtureTemplateA.php');
        $assetsManager->resolveAssetsList();
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->fileParser = new FilesParser();
        $this->container = new Container();
        $this->assetsManager = $this->container->get(AssetsListManager::class);
        $this->fixtTemplateA = 'fixtureTemplateA.php';
    }


    /**
     * Тест - правильно ли получаем список вьюшек из контроллера.
     */
    public function testGetControllerViews()
    {
        $templates = $this->fileParser->getControllerViews(__DIR__ ."/FileParserTestControllerClass.php");

        self::assertIsArray($templates);
        self::assertCount(3, $templates);
        self::assertEquals('viewA.php', $templates[0]);
        self::assertEquals('viewB.php', $templates[1]);
        self::assertEquals('viewC.php', $templates[2]);
    }



    /**
     * Тест - правильно ли получаем список js-ресурсов из файла вьюшки
     */
    public function testTemplateJsAssets()
    {
        $jsAssets = $this->fileParser->getTemplateAssets('fixtureTemplateA.php', AssetsListManager::RES_TYPE_JS);
        self::assertEquals($jsAssets, ['/js/fixture_b.js', '/js/fixture_c.js', '/js/fixture_d.js']);
    }



    /**
     * Тест - правильно ли получаем список css-ресурсов из файла вьюшки
     */
    public function testTemplateCssAssets()
    {
        $jsAssets = $this->fileParser->getTemplateAssets('fixtureTemplateA.php', AssetsListManager::RES_TYPE_CSS);
        self::assertEquals($jsAssets, ['/css/a.css', '/css/b.css']);
    }




    public function testAssets()
    {
        /**
         * 1. Проверить, как записывается файл в лист, его версии и пр., не дублируется ли.
         * 2. Как сохраняется файл.
         * 3. Корректное ли имя и версия.
         * 4. Правильно ли считаются версии.
         */
        $assetsList = $this->assetsManager->getAssetsList();

        $this->runBuildImitation();

        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_CSS]['/css/a.css']['version']);
        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_CSS]['/css/b.css']['version']);
        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_JS]['/js/fixture_b.js']['version']);
        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_JS]['/js/fixture_c.js']['version']);
        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_JS]['/js/fixture_d.js']['version']);

        self::assertEquals(
            ($assetsList->getTemplateAssets($this->fixtTemplateA))[AssetsListManager::RES_TYPE_JS]['assets'],
            ['/js/fixture_b.js', '/js/fixture_c.js', '/js/fixture_d.js']
        );
        self::assertEquals(
            ($assetsList->getTemplateAssets($this->fixtTemplateA))[AssetsListManager::RES_TYPE_CSS]['assets'],
            ['/css/a.css', '/css/b.css']
        );

        self::assertEquals(1, $assetsList->getTemplateAssetVersion($this->fixtTemplateA, AssetsListManager::RES_TYPE_JS));
        self::assertEquals(1, $assetsList->getTemplateAssetVersion($this->fixtTemplateA, AssetsListManager::RES_TYPE_CSS));

        /** Imitate update asset's file */
        /** Имитируем обновление файла и проверяем заново версии. */
        unlink(__DIR__ . '/../../../../web/js/fixture_c.js');
        sleep(1);
        file_put_contents(__DIR__ . '/../../../../web/js/fixture_c.js', '');

        $this->runBuildImitation();

        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_CSS]['/css/a.css']['version']);
        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_CSS]['/css/b.css']['version']);
        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_JS]['/js/fixture_b.js']['version']);
        self::assertEquals(2, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_JS]['/js/fixture_c.js']['version']);
        self::assertEquals(1, $assetsList->getAllAssetsList()[AssetsListManager::RES_TYPE_JS]['/js/fixture_d.js']['version']);

        self::assertEquals(
            ($assetsList->getTemplateAssets($this->fixtTemplateA))[AssetsListManager::RES_TYPE_JS]['assets'],
            ['/js/fixture_b.js', '/js/fixture_c.js', '/js/fixture_d.js']
        );
        self::assertEquals(
            ($assetsList->getTemplateAssets($this->fixtTemplateA))[AssetsListManager::RES_TYPE_CSS]['assets'],
            ['/css/a.css', '/css/b.css']
        );

        self::assertEquals(2, $assetsList->getTemplateAssetVersion($this->fixtTemplateA, AssetsListManager::RES_TYPE_JS));
        self::assertEquals(1, $assetsList->getTemplateAssetVersion($this->fixtTemplateA, AssetsListManager::RES_TYPE_CSS));

    }

    private function runBuildImitation()
    {
        $this->assetsManager->resolveTemplateAssets(
            $this->fixtTemplateA,
            ['/css/a.css', '/css/b.css'],
            AssetsListManager::RES_TYPE_CSS
        );
        $this->assetsManager->resolveTemplateAssets(
            $this->fixtTemplateA,
            ['/js/fixture_b.js', '/js/fixture_c.js', '/js/fixture_d.js'],
            AssetsListManager::RES_TYPE_JS
        );
        $this->assetsManager->resolveAssetsList();
    }

    private function removeFixtures()
    {
        $container = new Container();
        $assetsManager = $container->get(AssetsListManager::class);

        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_b.js', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_c.js', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_d.js', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/a.css', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/b.css', AssetsListManager::RES_TYPE_CSS);


        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_b.js', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_c.js', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/js/fixture_d.js', AssetsListManager::RES_TYPE_CSS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/a.css', AssetsListManager::RES_TYPE_JS);
        $assetsManager->getAssetsList()->removeFromAssetsList('/css/b.css', AssetsListManager::RES_TYPE_JS);

        $assetsManager->getAssetsList()->removeTemplate($this->fixtTemplateA);
        $assetsManager->resolveAssetsList();
    }
}