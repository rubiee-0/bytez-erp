<?php
session_start();
require_once 'config/database.php';
require_once 'app/helpers/auth_helper.php';

$url = isset($_GET['url']) ? trim($_GET['url'], '/') : '';

if (empty($url)) {
    if (isLoggedIn()) {
        header('Location: /bytez-erp/dashboard/index');
    } else {
        header('Location: /bytez-erp/auth/login');
    }
    exit();
}

$segments = explode('/', $url);
$controllerName = ucfirst($segments[0]) . 'Controller';
$method = $segments[1] ?? 'index';
$param = $segments[2] ?? null;

$controllerFile = "app/controllers/{$controllerName}.php";

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    if (method_exists($controller, $method)) {
        $controller->$method($param);
    } else {
        http_response_code(404);
        echo "<h2>Page not found</h2>";
    }
} else {
    http_response_code(404);
    echo "<h2>Controller not found: $controllerName</h2>";
}
//Test commit for system integration