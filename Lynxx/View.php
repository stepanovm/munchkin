<?php


namespace Lynxx;


use Laminas\Diactoros\Response\HtmlResponse;
use Lynxx\Exception\NotFoundException;
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
     * @param string $layout absolute path to layout file
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
        if (!file_exists(__DIR__ . '/../../web' . $css_file_path)) {
            //Utils::writeLog('app_errors', 'не удалось подключить css: '.$css_file_path.'. Файл не найден');
            return;
        }

        if (in_array($css_file_path, $this->css_paths)) {
            return;
        }
        $this->css_paths[] = $css_file_path;
    }

    /**
     * register js tag.
     * @param string $js path to js file
     * @param array $params (params for js tag, like 'async'. Exam: array('async', 'head'))
     */
    public function registerJs($js, $params = array())
    {
        /** if file not exist, write log and return */
        if (!file_exists(__DIR__ . '/../../web' . $js)) {
            //Utils::writeLog('app_errors', 'не удалось подключить js: '.$js.'. Файл не найден');
            return;
        }

        // if by some miracle the script is NOT asynchronous, just put tag to heads
        if (!in_array('async', $params)) {
            $jsTag = '<script type="text/javascript" src="' . $js . '"></script>';
            $this->registerHeadsTag($jsTag);
            return;
        }

        if (in_array('no_compress', $params)) {
            $jsTag = '<script async type="text/javascript" src="' . $js . '"></script>';
            $this->registerHeadsTag($jsTag);
            return;
        }

        if (in_array($js, $this->js_paths)) {
            return;
        }
        $this->js_paths[] = $js;
    }


    public function render($view_file, $data = [])
    {
        extract($data);

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
    public function registerComponent($name, $component_file, $data = array())
    {
        ob_start();
        $component_file = __DIR__ . '/../../' . $component_file;

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
        $headshtml = '';
        for ($i = 0; $i < count($heads); $i++) {
            $headshtml .= $heads[$i];
        }

        return $headshtml;
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