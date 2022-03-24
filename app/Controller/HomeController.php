<?php

namespace app\Controller;

use Lynxx\AbstractController;
use Lynxx\View;


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
            'name' => 'composer require bvanhoekelen/php-compressor'
        ]);
    }
}