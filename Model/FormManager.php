<?php

require_once('Model/UserManager.php');

class FormManager
{
    public function checkRegister($firstname, $lastname, $username, $email, $password, $password_repeat)
    {
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
            $data[] = 'this username is already used';
        }
        if (!empty($usersByEmail)) {
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
        $data = [];
        if (!file_exists($pathFile . '/' . $oldName)) {
            $data[] = 'the file you are trying to rename does not exist';
        }
        if (strlen($newName) == 0) {
            $data[] = 'the name is too short';
        } else if (file_exists($pathFile . '/' . $newName)) {
            $data[] = 'the file already exists';
        }
        $count = 0;
        for ($i = 0; $i < strlen($newName); $i++){
            if ('.' == $newName[$i]){
                $count++;
            }
        }
        if ($count == strlen($newName)){
            $data[] = 'invalid name';
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