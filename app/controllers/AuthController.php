<?php
require_once 'app/models/UserModel.php';

class AuthController
{
    public function login($param = null)
    {
        if (isLoggedIn()) {
            header('Location: /bytez-erp/dashboard/index');
            exit();
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                $model = new UserModel();
                $user = $model->findByEmail($email);
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    header('Location: /bytez-erp/dashboard/index');
                    exit();
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }
        require_once 'views/auth/login.php';
    }

    public function logout($param = null)
    {
        session_destroy();
        header('Location: /bytez-erp/auth/login');
        exit();
    }
}