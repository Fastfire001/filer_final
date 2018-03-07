<?php

require_once('Cool/BaseController.php');
require_once('Model/FormManager.php');
require_once('Model/UserManager.php');
require_once('Model/FileManager.php');
require_once('Model/userlog.php');
session_start();

class MainController extends BaseController
{
    public function homeAction()
    {
        $fileManager = new FileManager();
        $path = '';
        if (empty($_SESSION)){
            return $this->render('home.html.twig');
          
        }
        if (isset($_SESSION['username'])) {
            $data['username'] = $_SESSION['username'];
            writeToLog(userTracker('was connected'), 'access');
        }

        if (isset($_GET['download'])) {
            $fileManager->download($_GET['download']);
           // writeToLog(userTracker('download'($_GET['download'])), 'access');
        }
        if (isset($_GET['path'])) {
            $path = $_GET['path'];
            $data['path'] = $path;
        }
        if (isset($_FILES['userfile']) || isset($_POST['fileName'])){  
   
         
                       $fileName = explode('/', $_POST['fileName']);    
                        $fileName = array_filter($fileName);    
                        $fileName = array_values($fileName);    
                        //writeToLog(usertracker('download '. $_FILES['userfile']), 'access');/////////////////////////
                        for ($i = 0; $i < sizeof($fileName); $i++){    
                            if ('..' == $fileName[$i] || '.' == $fileName[$i]){    
                            unset($fileName[$i]);    
                            }    
                        }    
                        $fileName = array_values($fileName);    
                        $fileName = implode('/', $fileName);    
                        $pathFile = '';    
                        if (isset($_GET['path'])){    
                            $pathFile = explode('/', $_GET['path']);    
                            $pathFile = array_filter($pathFile);    
                            $pathFile = array_values($pathFile);    
                            for ($i = 0; $i < sizeof($pathFile); $i++){    
                                if ('..' == $pathFile[$i] || '.' == $pathFile[$i]){    
                                    unset($pathFile[$i]);    
                                }    
                            }    
                            $pathFile = array_values($pathFile);    
                            $pathFile = implode('/', $pathFile);    
                        }    
                        $result = $fileManager->uploadFile($fileName, $_FILES['userfile'], $pathFile);   
                        //writeToLog(userTracker('uploaded ' )  , 'access'); 
                        if ('ok' !== $result){    
                            $data['errors'] = $result;    
                        }    
                    }

        if (!empty($_POST['new-name']) || !empty($_POST['old-name'])) {
            $formManager = new FormManager();
            $oldName = $_POST['old-name'];
            $pathFile = './Uploads/' . $_SESSION['id'] . $_POST['path'];
            $newName = $formManager->deleteSpecialCharacter($_POST['new-name']);     //TO UPGRADE.......................
            $result = $formManager->checkRename($newName, $pathFile, $oldName);
            if ('ok' == $result) {
                $oldPath = $pathFile . '/' . $oldName;
                $newPath = $pathFile . '/' . $newName;
                $fileManager->rename($oldPath, $newPath);
            } else {
                $data['errors'] = $result;
            }
        }

        if (isset($_POST['moove-next-path']) && !empty($_POST['input-hidden-moove'])){
            $newPath = $fileManager->securisePath($_POST['moove-next-path']);
            $oldPath = $fileManager->securisePath($_POST['input-hidden-moove']);
            $name = explode('/', $oldPath);
            $name = $name[sizeof($name) - 1];
            $result = $fileManager->moove($oldPath, $newPath, $name);
            if ('ok' !== $result){
                $data['errors'] = $result;
            }
        }

        if (!empty($_POST['delete-path'])){
            $deletePath = explode('/', $_POST['delete-path']);
            $deletePath = array_filter($deletePath);
            $deletePath = array_values($deletePath);
            for ($i = 0; $i < sizeof($deletePath); $i++){
                if ('..' == $deletePath[$i] || '.' == $deletePath[$i]){
                    unset($deletePath[$i]);
                }
            }
            $deletePath = array_values($deletePath);
            $deletePath = implode('/', $deletePath);
            $fileManager->delete($deletePath);
        }
        $dirContent = $fileManager->scanDir($path);
        $data['dirs'] = $dirContent['0'];
        $data['files'] = $dirContent['1'];

        if (!empty($_GET['path'])) {
            $path = explode('/', $_GET['path']);
            $path = array_filter($path);
            $path = array_values($path);
            $dirNav[0] = '/' . $path[0];
            $dirNavT = [];
            for ($i = 1; $i < sizeof($path); $i++){
                $dirNav[$i] = $dirNav[$i - 1] . '/' . $path[$i];
            }
            for ($i = 0; $i < sizeof($path); $i++){
                $dirNavT[$i] = [
                    'path' => $dirNav[$i],
                    'name' => $path[$i]
                ];
            }
            $data['dirNav'] = $dirNavT;
        } else {
            $data['dirNav'] = [];
        }
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
        //writeToLog(userTracker('disconnected'), 'access');/////////////////////////////////////
        return $this->redirectToRoute('home');
      
    }
}
