<?php

require_once('Cool/BaseController.php');
require_once('Model/FormManager.php');
require_once('Model/UserManager.php');
require_once('Model/FileManager.php');
require_once('Model/LogManager.php');
session_start();

class MainController extends BaseController
{
    public function homeAction()
    {
        $fileManager = new FileManager();
        $logManager = new LogManager();
        $path = '';
        if (empty($_SESSION)) {
            return $this->render('home.html.twig');
        }
        if (isset($_SESSION['username'])) {
            $data['username'] = $_SESSION['username'];
        }

        if (isset($_GET['path'])) {
            $path = $_GET['path'];
            $data['path'] = $path;
        }
        //UPLOAD
        if (isset($_FILES['userfile']) || isset($_POST['fileName'])) {
            $fileName = $fileManager->securisePath($_POST['fileName']);
            $pathFile = '';
            if (isset($_GET['path'])) {
                $pathFile = $fileManager->securisePath($_GET['path']);
            }
            $result = $fileManager->uploadFile($fileName, $_FILES['userfile'], $pathFile);
            if ('ok' !== $result) {
                $data['errors'] = $result;
            }
        }
        //RENAME
        if (!empty($_POST['new-name']) || !empty($_POST['old-name'])) {
            $formManager = new FormManager();
            $oldName = $_POST['old-name'];
            $pathFile = './Uploads/' . $_SESSION['id'] . $_POST['path'];
            $newName = $fileManager->securisePath($_POST['new-name']);
            $result = $formManager->checkRename($newName, $pathFile, $oldName);
            if ('ok' == $result) {
                $oldPath = $pathFile . '/' . $oldName;
                $newPath = $pathFile . '/' . $newName;
                $fileManager->rename($oldPath, $newPath);
                $logManager->writeToLog('rename ' . $oldPath . ' -> ' . $newPath, false);
            } else {
                $data['errors'] = $result;
            }
        }
        //Move
        if (isset($_POST['moove-next-path']) && !empty($_POST['input-hidden-moove'])) {
            $newPath = $fileManager->securisePath($_POST['moove-next-path']);
            $oldPath = $fileManager->securisePath($_POST['input-hidden-moove']);
            $name = explode('/', $oldPath);
            $name = $name[sizeof($name) - 1];
            $result = $fileManager->moove($oldPath, $newPath, $name);
            if ('ok' !== $result) {
                $data['errors'] = $result;
            }
        }
        //delete
        if (!empty($_POST['delete-path'])) {
            $deletePath = $fileManager->securisePath($_POST['delete-path']);
            $fileManager->delete($deletePath);
        }
        $dirContent = $fileManager->scanDir($path);
        $data['dirs'] = $dirContent['0'];
        $files = $fileManager->checkExt($dirContent['1']);
        $data['files'] = $files;

        if (!empty($_GET['path'])) {
            $path = $fileManager->securisePath($_GET['path']);
            $path = explode('/', $path);
            $dirNav[0] = $path[0];
            $dirNavT = [];
            for ($i = 1; $i < sizeof($path); $i++) {
                $dirNav[$i] = $dirNav[$i - 1] . '/' . $path[$i];
            }
            for ($i = 0; $i < sizeof($path); $i++) {
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

    public function downloadAction()
    {
        $fileManager = new FileManager();
        $fileManager->download($_GET['dlPath']);
    }

    public function registerAction()
    {
        $logManager = new LogManager();
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
            $logManager->writeToLog('Create the account with the id ' . $user['id'], false);
            return $this->redirectToRoute('login');
        }
        return $this->render('register.html.twig');
    }

    public function loginaction()
    {
        $logManager = new LogManager();
        if (!empty($_SESSION)) {
            $logManager->writeToLog('try to go on action=login');
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
                $logManager->writeToLog('connect to account with the id ' . $user['id'], false);
                $this->redirectToRoute('home');
            } else {
                $logManager->writeToLog('try to connect on account ' . $username);
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
        $logManager = new LogManager();
        $userManager->logout();
        $logManager->writeToLog('disconnected', false);
        return $this->redirectToRoute('home');
    }

    public function viewaction()
    {
        $fileManager = new FileManager();
        $logManager = new LogManager();
        if (empty($_SESSION['id'])) {
            $logManager->writeToLog('try to go on action=view');
        }
        if (isset($_POST['file-content'])) {
            $logManager->writeToLog('Write in the file ' . $_POST['file'], false);
            file_put_contents($_POST['file'], $_POST['file-content']);
            $data['close'] = 'You can close this window';
            return $this->render('view.html.twig', $data);
        }
        $path = ['./uploads/' . $_SESSION['id'] . '/' . $fileManager->securisePath($_GET['path'])];
        $result = $fileManager->checkExt($path)['0'];
        if (!is_file($path['0'])) {
            $errors[] = 'Can not display this file type';
            $logManager->writeToLog('try to display the file ' . $path[0]);
        }
        if (isset($result['img'])) {
            $data = [
                'ext' => 'img',
                'name' => basename($result['name']),
                'file' => $result['name']
            ];
        } elseif (isset($result['audio'])) {
            $data = [
                'ext' => 'audio',
                'file' => $result['name']
            ];
        } elseif (isset($result['video'])) {
            $data = [
                'ext' => 'video',
                'file' => $result['name']
            ];
        } elseif (isset($result['write'])) {
            $data = [
                'ext' => 'txt',
                'file' => $result['name'],
                'fileContent' => file_get_contents($result['name'])
            ];
        } else {
            $logManager->writeToLog('try to display a file with the wrong extention->' . $path[0]);
        }
        $data['username'] = $_SESSION['username'];
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }
        $path = $fileManager->securisePath($_GET['path']);
        $path = explode('/', $path);
        unset($path[sizeof($path) - 1]);
        $path = array_values($path);
        $data['path'] = implode('/', $path);
        return $this->render('view.html.twig', $data);
    }
}
