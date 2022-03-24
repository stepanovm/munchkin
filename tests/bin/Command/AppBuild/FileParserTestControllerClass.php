<?php

namespace tests\bin\Command\AppBuild;

use Lynxx\AbstractController;
use Lynxx\View;

class FileParserTestControllerClass extends AbstractController
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function actionA()
    {
        return $this->view->render('viewA.php', [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
        ]);
    }

    public function actionB()
    {
        return $this->view->render('viewB.php', [
            'key1' => 'value1',
        ]);
    }

    public function actionC()
    {
        return $this->view->render('viewC.php');
    }

    public function actionD()
    {
        return $this->view->render('viewC.php');
    }
}