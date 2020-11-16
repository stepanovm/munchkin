<?php


namespace app\Controller;


use Lynxx\AbstractController;

class HomeController extends AbstractController
{
    public function home()
    {
        echo '<h1>Munchkin Game</h1>';
    }
}