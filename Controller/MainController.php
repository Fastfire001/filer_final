<?php

require_once('Cool/BaseController.php');
require_once('Model/FormManager.php');
require_once('Model/UserManager.php');
require_once('Model/FileManager.php');
session_start();

class MainController extends BaseController
{
    public function homeAction()
    {
        $fileManager = new FileManager();
        $path = $_SESSION['id'];

        if (isset($_GET['download'])){
            $fileManager->download($_GET['download']);
        }
        if (isset($_GET['path'])){
            $path = $path . $_GET['path'];
            $data['path'] = $_GET['path'];
        }
        if (!empty($_POST['new-name']) || !empty($_POST['old-name'])){
            $newName = htmlentities($_POST['new-name']);
            $oldName = htmlentities($_POST['old-name']);
            $pathFile = htmlentities($_POST['path']);
            $formManager = new FormManager();
            $newName = $formManager->deleteSpecialCharacter($newName);
            if (empty($pathFile)){
                $pathFile = './Uploads/' . $_SESSION['id'];
            } else {
                $pathFile = './Uploads/' . $_SESSION['id'] . $pathFile;
            }
            $result = $formManager->checkRenameFile($newName, $pathFile, $oldName);
            if ('ok' == $result){
                rename($pathFile . '/' . $oldName, $pathFile . '/' . $newName);
                var_dump($_GET['path']);
            } else {
                $data['errors'] = $result;
            }
        }
        $dirContent = $fileManager->scanDir($path);
        var_dump($path);
        var_dump($dirContent);
        $data['dirs'] = $dirContent['0'];
        $data['files'] = $dirContent['1'];
        return $this->render('home.html.twig', $data);
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
            $user = $userManager->getUserByUsername($username);
            mkdir('Uploads/' . $user['id']);
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
                $user = $userManager->getUserByUsername($username);
                $userManager->login($username, $user['id']);
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
