<?php
require_once 'app/models/ClientModel.php';

class ClientsController
{
    private $model;

    public function __construct()
    {
        $this->model = new ClientModel();
    }

    public function index($param = null)
    {
        requireLogin();
        $clients = $this->model->getAll();
        $pageTitle = 'Clients';
        require_once 'views/layouts/header.php';
        require_once 'views/clients/index.php';
        require_once 'views/layouts/footer.php';
    }

    public function create($param = null)
    {
        requireRole('admin', 'manager');
        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'company_name' => trim($_POST['company_name'] ?? ''),
                'industry' => trim($_POST['industry'] ?? ''),
                'contact_name' => trim($_POST['contact_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'created_by' => $_SESSION['user_id'],
            ];
            if (empty($data['company_name'])) {
                $error = 'Company name is required.';
            } else {
                $this->model->create($data);
                header('Location: /bytez-erp/clients/index');
                exit();
            }
        }
        $pageTitle = 'Add Client';
        require_once 'views/layouts/header.php';
        require_once 'views/clients/create.php';
        require_once 'views/layouts/footer.php';
    }

    public function edit($id = null)
    {
        requireRole('admin', 'manager');
        $client = $this->model->findById($id);
        $error = '';
        if (!$client) {
            die('Client not found');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'company_name' => trim($_POST['company_name'] ?? ''),
                'industry' => trim($_POST['industry'] ?? ''),
                'contact_name' => trim($_POST['contact_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
            ];
            if (empty($data['company_name'])) {
                $error = 'Company name is required.';
            } else {
                $this->model->update($id, $data);
                header('Location: /bytez-erp/clients/index');
                exit();
            }
        }
        $pageTitle = 'Edit Client';
        require_once 'views/layouts/header.php';
        require_once 'views/clients/edit.php';
        require_once 'views/layouts/footer.php';
    }

    public function delete($id = null)
    {
        requireRole('admin');
        $this->model->delete($id);
        header('Location: /bytez-erp/clients/index');
        exit();
    }

    public function view($id = null)
    {
        requireLogin();
        $client = $this->model->findById($id);
        if (!$client) {
            die('Client not found');
        }

        // Fetch invoices from Laravel billing system
        $invoices = [];
        $api_url = 'http://localhost/laravel-invoice-billing-system/public/api/clients/' . $client['id'] . '/invoices';
        $api_token = 'be9408a6f871f2afa2b741435728fd2cb815e65aa972e8414f2077e24bc50d23';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $api_token,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success']) {
                $invoices = $data['data'];
            }
        }

        $pageTitle = 'Client Details - ' . htmlspecialchars($client['company_name']);
        require_once 'views/layouts/header.php';
        require_once 'views/clients/view.php';
        require_once 'views/layouts/footer.php';
    }
}