<?php

require_once('Cool/DBManager.php');

class UserManager
{
    public function getUserById($id)
    {
        $dbm = DBManager::getInstance();
        $pdo = $dbm->getPdo();

        $result = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $result->execute([':id' => $id]);
        $post = $result->fetch();

        return $post;
    }

    public function getUserByUsername($username)
    {
        $dbm = DBManager::getInstance();
        $pdo = $dbm->getPdo();

        $result = $pdo->prepare('SELECT * FROM users WHERE username = :username');
        $result->execute([':username' => $username]);
        $users = $result->fetch();

        return $users;
    }

    public function getUserByEmail($email)
    {
        $dbm = DBManager::getInstance();
        $pdo = $dbm->getPdo();

        $result = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $result->execute([':email' => $email]);
        $users = $result->fetch();

        return $users;
    }

    public function addUser($firstname, $lastname, $username, $email, $password)
    {
        $dbm = DBManager::getInstance();
        $pdo = $dbm->getPdo();
        $result = $pdo->prepare('INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `password`) VALUES (NULL, :firstname, :lastname, :username, :email, :password)');
        $result->bindParam(':firstname', $firstname);
        $result->bindParam(':lastname', $lastname);
        $result->bindParam(':username', $username);
        $result->bindParam(':email', $email);
        $result->bindParam(':password', $password);
        $result->execute();
    }

    public function login($username, $id)
    {
        $_SESSION['username'] = $username;
        $_SESSION['id'] = $id;
    }

    public function logout()
    {
        session_destroy();
    }
}