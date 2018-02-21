<?php

class FormManager
{
    public function checkRegister($firstname, $lastname, $username, $email, $password, $password_repeat)
    {
        if (strlen($firstname) >= 2 && strlen($lastname) >= 2 && strlen($username) >= 2 && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6 && $password == $password_repeat) {
            return true;
        } else if (strlen($firstname) < 2) {
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
        return $data;
    }
}