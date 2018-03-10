<?php

require_once('Model/UserManager.php');
require_once('Model/LogManager.php');

class FormManager
{
    public function checkRegister($firstname, $lastname, $username, $email, $password, $password_repeat)
    {
        $logManager = new LogManager();
        if (strlen($firstname) < 2) {
            $data[] = "First name must be 2 characters or more";
        }
        if (strlen($lastname) < 2) {
            $data[] = "Last name must be 2 characters or more";
        }
        if (strlen($username) < 2) {
            $data[] = "Username must be 2 characters or more";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data[] = "Email must be valid";
        }
        if (strlen($password) < 6) {
            $data[] = "Password must be 6 characters or more";
        }
        if ($password !== $password_repeat) {
            $data[] = "Both passwords must be the same";
        }
        $userManager = new UserManager();
        $usersByUsername = $userManager->getUserByUsername($username);
        $usersByEmail = $userManager->getUserByEmail($email);
        if (!empty($usersByUsername)) {
            $logManager->writeToLog('try to create a account with a username already taken-> ' . $username);
            $data[] = 'this username is already used';
        }
        if (!empty($usersByEmail)) {
            $logManager->writeToLog('try to create a account with a email adress already taken-> ' . $email);
            $data[] = 'this email is already used';
        }
        if (empty($data)) {
            return true;
        }
        return $data;
    }

    public function checkLogin($username, $password)
    {
        if (empty($password)) {
            return false;
        }
        $userManager = new UserManager();
        $usersByUsername = $userManager->getUserByUsername($username);
        if ($password == $usersByUsername['password']) {
            return true;
        } else if (empty($usersByUsername)) {
            return false;
        } else {
            return false;
        }
    }

    public function checkRename($newName, $pathFile, $oldName)
    {
        $logManager = new LogManager();
        $data = [];
        if (!file_exists($pathFile . '/' . $oldName)) {
            $data[] = 'the file you are trying to rename does not exist';
            $logManager->writeToLog('Try to rename a file who does not exist->' . $pathFile . '/' . $oldName);
        }
        if (strlen($newName) == 0) {
            $data[] = 'the name is too short';
            $logManager->writeToLog('try to rename a file with a empty name');
        } else if (file_exists($pathFile . '/' . $newName)) {
            $data[] = 'the file already exists-> ' . $pathFile . '/' . $newName;
            $logManager->writeToLog('try to rename file with a name who already exist');
        }
        if (empty($data)){
            return 'ok';
        }
        return $data;
    }

    public function deleteSpecialCharacter($string){
        $char = [
            '/',
            ':',
            '"',
            "\\",
            '|',
            '?',
            '<',
            '>',
            '*',
            '!',
            ','
        ];
        for ($i = 0; $i < sizeof($char); $i++){
            $string = str_replace($char[$i], '', $string);
        }
        return $string;
    }
}