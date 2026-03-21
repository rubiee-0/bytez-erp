<?php
require_once 'app/models/TaskModel.php';
require_once 'app/models/ProjectModel.php';
require_once 'app/models/UserModel.php';

class TasksController {
    private $model;

    public function __construct() {
        $this->model = new TaskModel();
    }

    public function index($param = null) {
        requireLogin();
        $tasks     = $this->model->getAll();
        $pageTitle = 'Tasks';
        require_once 'views/layouts/header.php';
        require_once 'views/tasks/index.php';
        require_once 'views/layouts/footer.php';
    }

    public function create($param = null) {
        requireLogin();
        $error    = '';
        $projects = (new ProjectModel())->getAll();
        $users    = (new UserModel())->getAll();
        $selectedProject = $_GET['project_id'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'       => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'project_id'  => (int)($_POST['project_id'] ?? 0),
                'assigned_to' => (int)($_POST['assigned_to'] ?? 0),
                'created_by'  => $_SESSION['user_id'],
                'priority'    => $_POST['priority'] ?? 'medium',
                'status'      => $_POST['status'] ?? 'todo',
                'deadline'    => $_POST['deadline'] ?? '',
            ];

            if (empty($data['title'])) {
                $error = 'Task title is required.';
            } elseif (empty($data['project_id'])) {
                $error = 'Please select a project.';
            } elseif (empty($data['deadline'])) {
                $error = 'Deadline is required.';
            } else {
                $this->model->create($data);
                $redirect = $selectedProject
                    ? "/Codebytez/projects/view/{$data['project_id']}"
                    : '/Codebytez/tasks/index';
                header("Location: $redirect");
                exit();
            }
        }

        $pageTitle = 'Create Task';
        require_once 'views/layouts/header.php';
        require_once 'views/tasks/create.php';
        require_once 'views/layouts/footer.php';
    }

    public function view($id = null) {
        requireLogin();
        $task        = $this->model->findById($id);
        if (!$task) { die('Task not found'); }
        $comments    = $this->model->getComments($id);
        $attachments = $this->model->getAttachments($id);
        $pageTitle   = $task['title'];
        require_once 'views/layouts/header.php';
        require_once 'views/tasks/view.php';
        require_once 'views/layouts/footer.php';
    }

    public function edit($id = null) {
        requireLogin();
        $task     = $this->model->findById($id);
        if (!$task) { die('Task not found'); }
        $projects = (new ProjectModel())->getAll();
        $users    = (new UserModel())->getAll();
        $error    = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'       => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'project_id'  => (int)($_POST['project_id'] ?? 0),
                'assigned_to' => (int)($_POST['assigned_to'] ?? 0),
                'priority'    => $_POST['priority'] ?? 'medium',
                'status'      => $_POST['status'] ?? 'todo',
                'deadline'    => $_POST['deadline'] ?? '',
            ];

            if (empty($data['title'])) {
                $error = 'Task title is required.';
            } elseif (empty($data['project_id'])) {
                $error = 'Please select a project.';
            } elseif (empty($data['deadline'])) {
                $error = 'Deadline is required.';
            } else {
                $this->model->update($id, $data);
                header('Location: /Codebytez/tasks/view/' . $id);
                exit();
            }
        }

        $pageTitle = 'Edit Task';
        require_once 'views/layouts/header.php';
        require_once 'views/tasks/edit.php';
        require_once 'views/layouts/footer.php';
    }

    public function delete($id = null) {
        requireLogin();
        $this->model->delete($id);
        header('Location: /Codebytez/tasks/index');
        exit();
    }

    public function comment($id = null) {
        requireLogin();
        $comment = trim($_POST['comment'] ?? '');
        if (!empty($comment)) {
            $this->model->addComment($id, $_SESSION['user_id'], $comment);
        }
        header('Location: /Codebytez/tasks/view/' . $id);
        exit();
    }

    public function upload($id = null) {
        requireLogin();
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
            $uploadDir  = 'public/uploads/';
            $fileName   = time() . '_' . basename($_FILES['attachment']['name']);
            $filePath   = $uploadDir . $fileName;
            $fileType   = $_FILES['attachment']['type'];

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath)) {
                $this->model->addAttachment([
                    'task_id'   => $id,
                    'user_id'   => $_SESSION['user_id'],
                    'file_name' => basename($_FILES['attachment']['name']),
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                ]);
            }
        }
        header('Location: /Codebytez/tasks/view/' . $id);
        exit();
    }
}