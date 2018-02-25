<?php

require_once('Cool/BaseController.php');
require_once('Model/FormManager.php');
require_once('Model/UserManager.php');
session_start();

class MainController extends BaseController
{
    public function homeAction()
    {
        return $this->render('home.html.twig');
    }

    public function registerAction()
    {
        if (!empty($_SESSION)) {
            return $this->redirectToRoute('home');
        }
        if (!empty($_POST['firstname']) || !empty($_POST['lastname']) || !empty($_POST['username']) || !empty($_POST['email']) || !empty($_POST['password']) || !empty($_POST['password_repeat'])) {
            $firstname = htmlentities($_POST['firstname']);
            $lastname = htmlentities($_POST['lastname']);
            $username = htmlentities($_POST['username']);
            $email = htmlentities($_POST['email']);
            $password = $_POST['password'];
            $password_repeat = $_POST['password_repeat'];
            $formManager = new FormManager();
            $userManager = new UserManager();
            $form_result = $formManager->checkRegister($firstname, $lastname, $username, $email, $password, $password_repeat);
            if (true !== $form_result) {
                $data = [
                    'errors' => $form_result,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'username' => $username,
                    'email' => $email
                ];
                return $this->render('register.html.twig', $data);
            }
            $userManager->addUser($firstname, $lastname, $username, $email, $password);
            return $this->redirectToRoute('login');
        }
        return $this->render('register.html.twig');
    }

    public function loginaction()
    {
        if (!empty($_SESSION)) {
            return $this->redirectToRoute('home');
        }
        if (!empty($_POST['username']) || !empty($_POST['password'])) {
            $username = htmlentities($_POST['username']);
            $password = htmlentities($_POST['password']);
            $formManager = new FormManager();
            $result = $formManager->checkLogin($username, $password);
            if (true == $result) {
                $userManager = new UserManager();
                $userManager->login($username, $password);
                $this->redirectToRoute('home');
            } else {
                $data = [
                    'errors' => 'Wrong password or username',
                    'username' => $username
                ];
                return $this->render('login.html.twig', $data);
            }
        }
        return $this->render('login.html.twig');
    }

    public function logoutaction()
    {
        $userManager = new UserManager();
        $userManager->logout();
        return $this->redirectToRoute('home');
    }
}
