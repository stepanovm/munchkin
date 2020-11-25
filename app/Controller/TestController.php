<?php


namespace app\Controller;


use Lynxx\AbstractController;
use Lynxx\View;
use Psr\Container\ContainerInterface;

class TestController extends AbstractController
{
    private ContainerInterface $container;
    private View $view;

    public function __construct(View $view, ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $view;
    }


    public function test1()
    {
        return $this->view->render('home.php', [
            'name' => 'Lynxx',
            'container' => print_r($this->container, true)
        ]);
    }

    public function test2()
    {
        return $this->view->render('hometest2.php', [
            'name' => 'Lynxx',
            'container' => print_r($this->container, true)
        ]);
    }
}