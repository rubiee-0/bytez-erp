<?php
require_once 'app/models/DashboardModel.php';

class DashboardController {
    public function index($param = null) {
        requireLogin();
        $model          = new DashboardModel();
        $stats          = $model->getStats();
        $recentProjects = $model->getRecentProjects();
        $recentTasks    = $model->getRecentTasks();
        $taskChart      = $model->getTaskStatusCounts();
        $projectChart   = $model->getProjectStatusCounts();
        $pageTitle      = 'Dashboard';
        require_once 'views/layouts/header.php';
        require_once 'views/dashboard/index.php';
        require_once 'views/layouts/footer.php';
    }
}