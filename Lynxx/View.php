<?php


namespace Lynxx;


use bin\Command\AppBuild\AssetsListManager;
use Laminas\Diactoros\Response\HtmlResponse;
use Lynxx\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class View
{
    /** @var string $title text for <title> tag */
    protected $title;
    /** @var array $heads headtags array for <head> block */
    protected $heads = array();
    /** @var array $css_paths css_paths array for <head> block */
    protected $css_paths = array();
    /** @var array $js_paths js_paths array for <head> block */
    protected $js_paths = array();
    /** @var string $layout absolute path to layout file */
    protected $layout;
    /** @var string $layout rendered view html code */
    protected $content;
    /** @var array $data some data for rendered view (usually transmitted from controller) */
    protected $data = array();
    /** @var array $components contains array of rendered components (html code) */
    protected $components = array();

    protected ?string $templatePath = null;

    protected ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * add any data to $this->data
     * @param string $key
     * @param mixed $value
     */
    public function addData(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $layout path to layout file
     * <br /><br />Note: layout file must be placed here <b>/app/templates/layout/</b>
     */
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * @param string $title just title text
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * add new head tag to $this->heads array.
     * if tag already in array, just return;
     * @param string $tag full tag for <head> block
     */
    public function registerHeadsTag(string $tag): void
    {
        if (in_array($tag, $this->heads)) {
            return;
        }
        $this->heads[] = $tag;
    }

    /**
     * register css tag.
     * @param string $css_file_path
     */
    public function registerCss(string $css_file_path): void
    {
        /** if file not exist, write log and return */
        if (!file_exists(__DIR__ . '/../web' . $css_file_path)) {
            //Utils::writeLog('app_errors', 'не удалось подключить css: '.$css_file_path.'. Файл не найден');
            return;
        }

        if ($this->container->get('config')['application_mode'] === 'DEV') {
            $this->registerHeadsTag('<link href="' . $css_file_path . '" rel="stylesheet" type="text/css" />');
        }
    }

    /**
     * register js tag.
     * @param string $js path to js file
     */
    public function registerJs(string $js, array $params)
    {
        /** if file not exist, write log and return */
        if (!file_exists(__DIR__ . '/../web' . $js)) {
            /** writeLog('app_errors', 'не удалось подключить js: '.$js.'. Файл не найден'); */
            return;
        }

        $async = in_array('async', $params) ? 'async' : '';
        $jsTag = '<script ' . $async . ' type="text/javascript" src="' . $js . '"></script>';

        if (!in_array('async', $params)
            || in_array('nocompress', $params)
            || $this->container->get('config')['application_mode'] === 'DEV') {
            $this->registerHeadsTag($jsTag);
        }
    }


    public function render($view_file, $data = []): ResponseInterface
    {
        extract($data);

        $this->templatePath = $view_file;

        ob_start();
        $view_file = __DIR__ . '/../app/templates/' . $view_file;
        if (!file_exists($view_file)) {
            ob_end_clean();
            throw new NotFoundException('template not found');
        }
        include $view_file;
        $content = ob_get_contents();
        ob_end_clean();

        $layout = __DIR__ . '/../app/templates/layout/' . $this->layout;
        if (isset($this->layout) && file_exists($layout = __DIR__ . '/../app/templates/layout/' . $this->layout)) {
            ob_start();
            include $layout;
            $content = ob_get_contents();
            ob_end_clean();
        }

        return (new HtmlResponse($content))
            ->withAddedHeader('X-XSS-Protection', 1)
            ->withAddedHeader('X-Content-Type-Options', 'nosniff')
            ->withAddedHeader('Referrer-Policy', 'no-referrer-when-downgrade');
    }

    /**
     * Method add component html content in $this->components array.
     *
     * @param string $name component_name it will use as key in components array
     * @param string $component_file path to component file
     * @param array $data some data, can be used at component as $data['key']
     */
    public function registerComponent(string $name, string $component_file, array $data = [])
    {
        extract($data);

        ob_start();
        $component_file = __DIR__ . '/../app/templates/' . $component_file;

        if (!file_exists($component_file)) {
            echo 'Не удалось загрузить компонент ' . $name;
        } else {
            include $component_file;
        }

        $this->components[$name] = ob_get_contents();
        ob_end_clean();
    }

    /** @return string $title title text */
    public function getTitle()
    {
        return $this->title;
    }

    /** @return string $headshtml all registered head tags as string */
    public function getHeads()
    {
        $heads = $this->heads;
        $headsHtml = '';
        for ($i = 0; $i < count($heads); $i++) {
            $headsHtml .= $heads[$i];
        }

        if ($this->container->get('config')['application_mode'] === 'PROD') {
            $headsHtml .= $this->appendProdAssets();
        }

        return $headsHtml;
    }


    private function appendProdAssets(): string
    {
        $assetsHeadHtml = '';

        /** @var AssetsListManager $assetsManager */
        $assetsManager = $this->container->get(AssetsListManager::class);

        $jsFileInfo = $assetsManager->getCompressedAssetInfo($this->templatePath, AssetsListManager::RES_TYPE_JS);
        if (file_exists(__DIR__ . '/../web' . $jsFileInfo['filename'])) {
            $assetsHeadHtml .= '<script async type="text/javascript" src="' . $jsFileInfo['filename'] . '?'.$jsFileInfo['version'].'"></script>';
        }

        $cssFileInfo = $assetsManager->getCompressedAssetInfo($this->templatePath, AssetsListManager::RES_TYPE_CSS);
        if (file_exists(__DIR__ . '/../web' . $cssFileInfo['filename'])) {
            $assetsHeadHtml .= '<link href="' . $cssFileInfo['filename']  . '?'.$cssFileInfo['version'] . '" rel="stylesheet" type="text/css" />';
        }

        return $assetsHeadHtml;
    }

    /**
     * return component html code if exist
     * @param string $name
     * @return string component html code or ''
     */
    public function showComponent($name)
    {
        if (!array_key_exists($name, $this->components)) {
            return '';
        }
        return $this->components[$name];
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasComponent($name)
    {
        if (!array_key_exists($name, $this->components)) {
            return false;
        }
        return true;
    }


}