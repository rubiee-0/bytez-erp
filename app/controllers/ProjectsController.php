<?php
require_once 'app/models/ProjectModel.php';
require_once 'app/models/ClientModel.php';
require_once 'app/models/UserModel.php';

class ProjectsController {
    private $model;

    public function __construct() {
        $this->model = new ProjectModel();
    }

    public function index($param = null) {
        requireLogin();
        $projects  = $this->model->getAll();
        $pageTitle = 'Projects';
        require_once 'views/layouts/header.php';
        require_once 'views/projects/index.php';
        require_once 'views/layouts/footer.php';
    }

    public function create($param = null) {
        requireRole('admin', 'manager');
        $error   = '';
        $clients = (new ClientModel())->getAll();
        $users   = (new UserModel())->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'       => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'client_id'   => (int)($_POST['client_id'] ?? 0),
                'manager_id'  => (int)($_POST['manager_id'] ?? 0),
                'start_date'  => $_POST['start_date'] ?? '',
                'deadline'    => $_POST['deadline'] ?? '',
                'budget'      => (float)($_POST['budget'] ?? 0),
                'status'      => $_POST['status'] ?? 'pending',
                'progress'    => (int)($_POST['progress'] ?? 0),
            ];
            if (empty($data['title'])) {
                $error = 'Project title is required.';
            } elseif (empty($data['client_id'])) {
                $error = 'Please select a client.';
            } elseif (empty($data['start_date'])) {
                $error = 'Start date is required.';
            } elseif (empty($data['deadline'])) {
                $error = 'Deadline is required.';
            } elseif ($data['deadline'] < $data['start_date']) {
                $error = 'Deadline cannot be before start date.';
            } elseif ($data['budget'] < 0) {
                $error = 'Budget cannot be negative.';
            } elseif ($data['progress'] < 0 || $data['progress'] > 100) {
                $error = 'Progress must be between 0 and 100.';
            } else {
                $this->model->create($data);
                $projectId = $this->model->lastId ?? null;
                if (!empty($_POST['members']) && $projectId) {
                    foreach ($_POST['members'] as $uid) {
                        $this->model->addMember($projectId, (int)$uid);
                    }
                }
                header('Location: /Codebytez/projects/index');
                exit();
            }
        }
        $pageTitle = 'Create Project';
        require_once 'views/layouts/header.php';
        require_once 'views/projects/create.php';
        require_once 'views/layouts/footer.php';
    }

    public function view($id = null) {
        requireLogin();
        $project   = $this->model->findById($id);
        if (!$project) { die('Project not found'); }
        $members   = $this->model->getMembers($id);
        $tasks     = $this->model->getTasks($id);
        $pageTitle = $project['title'];
        require_once 'views/layouts/header.php';
        require_once 'views/projects/view.php';
        require_once 'views/layouts/footer.php';
    }

    public function edit($id = null) {
        requireRole('admin', 'manager');
        $project = $this->model->findById($id);
        if (!$project) { die('Project not found'); }
        $clients = (new ClientModel())->getAll();
        $users   = (new UserModel())->getAll();
        $members = $this->model->getMembers($id);
        $memberIds = array_column($members, 'id');
        $error   = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'       => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'client_id'   => (int)($_POST['client_id'] ?? 0),
                'manager_id'  => (int)($_POST['manager_id'] ?? 0),
                'start_date'  => $_POST['start_date'] ?? '',
                'deadline'    => $_POST['deadline'] ?? '',
                'budget'      => (float)($_POST['budget'] ?? 0),
                'status'      => $_POST['status'] ?? 'pending',
                'progress'    => (int)($_POST['progress'] ?? 0),
            ];
            if (empty($data['title'])) {
                $error = 'Project title is required.';
            } elseif (empty($data['client_id'])) {
                $error = 'Please select a client.';
            } elseif (empty($data['start_date'])) {
                $error = 'Start date is required.';
            } elseif (empty($data['deadline'])) {
                $error = 'Deadline is required.';
            } elseif ($data['deadline'] < $data['start_date']) {
                $error = 'Deadline cannot be before start date.';
            } elseif ($data['budget'] < 0) {
                $error = 'Budget cannot be negative.';
            } elseif ($data['progress'] < 0 || $data['progress'] > 100) {
                $error = 'Progress must be between 0 and 100.';
            } else {
                $this->model->update($id, $data);
                $this->model->removeMembers($id);
                if (!empty($_POST['members'])) {
                    foreach ($_POST['members'] as $uid) {
                        $this->model->addMember($id, (int)$uid);
                    }
                }
                header('Location: /Codebytez/projects/index');
                exit();
            }
        }
        $pageTitle = 'Edit Project';
        require_once 'views/layouts/header.php';
        require_once 'views/projects/edit.php';
        require_once 'views/layouts/footer.php';
    }

    public function delete($id = null) {
        requireRole('admin');
        $this->model->delete($id);
        header('Location: /Codebytez/projects/index');
        exit();
    }
}