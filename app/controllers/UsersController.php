<?php
require_once 'app/models/UserModel.php';

class UsersController {
    private $model;

    public function __construct() {
        $this->model = new UserModel();
    }

    public function index($param = null) {
        requireRole('admin');
        $users     = $this->model->getAll();
        $pageTitle = 'Users Management';
        require_once 'views/layouts/header.php';
        require_once 'views/users/index.php';
        require_once 'views/layouts/footer.php';
    }

    public function create($param = null) {
        requireRole('admin');
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'     => trim($_POST['name'] ?? ''),
                'email'    => trim($_POST['email'] ?? ''),
                'password' => trim($_POST['password'] ?? ''),
                'role'     => $_POST['role'] ?? 'employee',
            ];
            if (empty($data['name'])) {
                $error = 'Name is required.';
            } elseif (empty($data['email'])) {
                $error = 'Email is required.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            } elseif (empty($data['password'])) {
                $error = 'Password is required.';
            } elseif (strlen($data['password']) < 6) {
                $error = 'Password must be at least 6 characters.';
            } else {
                $this->model->create($data);
                header('Location: /Codebytez/users/index');
                exit();
            }
        }
        $pageTitle = 'Add User';
        require_once 'views/layouts/header.php';
        require_once 'views/users/create.php';
        require_once 'views/layouts/footer.php';
    }

    public function edit($id = null) {
        requireRole('admin');
        $user  = $this->model->findById($id);
        if (!$user) { die('User not found'); }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'  => trim($_POST['name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'role'  => $_POST['role'] ?? 'employee',
            ];
            if (empty($data['name'])) {
                $error = 'Name is required.';
            } elseif (empty($data['email'])) {
                $error = 'Email is required.';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format.';
            } else {
                $this->model->update($id, $data);
                // Update password if provided
                if (!empty($_POST['password'])) {
                    if (strlen($_POST['password']) < 6) {
                        $error = 'Password must be at least 6 characters.';
                    } else {
                        $this->model->updatePassword($id, $_POST['password']);
                    }
                }
                if (empty($error)) {
                    header('Location: /Codebytez/users/index');
                    exit();
                }
            }
        }
        $pageTitle = 'Edit User';
        require_once 'views/layouts/header.php';
        require_once 'views/users/edit.php';
        require_once 'views/layouts/footer.php';
    }

    public function delete($id = null) {
        requireRole('admin');
        if ($id == $_SESSION['user_id']) {
            die('You cannot delete yourself!');
        }
        $this->model->delete($id);
        header('Location: /Codebytez/users/index');
        exit();
    }
}