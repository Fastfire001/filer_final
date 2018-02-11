<?php

require_once('Cool/BaseController.php');

class MainController extends BaseController
{
    public function homeAction()
    {
        return $this->render('home.html.twig');
    }

    public function registerAction()
    {
        return $this->render('register.html.twig');
    }

    public function loginaction()
    {
        return $this->render('login.html.twig');
    }
}
