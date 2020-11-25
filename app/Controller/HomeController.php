<?php


namespace app\Controller;


use Lynxx\AbstractController;
use Lynxx\View;
use Psr\Container\ContainerInterface;

class HomeController extends AbstractController
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function home()
    {
        return $this->view->render('home.php', [
            'name' => 'Lynxx'
        ]);
    }
}