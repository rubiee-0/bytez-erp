<?php
/**
 * Billing System Integration Controller
 * 
 * Syncs clients from Bytez ERP to Laravel Billing System
 * 
 * @config Update the constants below for your environment
 */
class BillingSyncController
{

    // Billing system configuration
    const BILLING_API_URL = 'http://127.0.0.1:8000/api/sync-clients';
    const BILLING_API_TOKEN = 'be9408a6f871f2afa2b741435728fd2cb815e65aa972e8414f2077e24bc50d23';

    /**
     * Sync a single client to billing system
     */
    public static function syncClient($clientData)
    {
        $payload = [
            'external_id' => 'bytez-' . $clientData['id'],
            'name' => $clientData['contact_name'] ?: $clientData['company_name'],
            'email' => $clientData['email'],
            'phone' => $clientData['phone'],
            'address' => $clientData['address'],
            'company' => $clientData['company_name']
        ];

        return self::sendToBilling($payload);
    }

    /**
     * Sync all clients to billing system
     */
    public static function syncAllClients($db)
    {
        $result = $db->query("SELECT * FROM clients WHERE status = 'active'");
        $clients = $result->fetch_all(MYSQLI_ASSOC);

        $synced = 0;
        $failed = 0;
        $errors = [];

        foreach ($clients as $client) {
            $response = self::syncClient($client);
            if ($response['success']) {
                $synced++;
            } else {
                $failed++;
                $errors[] = "Client ID {$client['id']}: " . $response['message'];
            }
        }

        return [
            'synced' => $synced,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Send client data to billing system via API
     */
    private static function sendToBilling($data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::BILLING_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Token: ' . self::BILLING_API_TOKEN
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 201 || $httpCode === 200) {
            return ['success' => true, 'data' => $result];
        } else {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Unknown error',
                'http_code' => $httpCode
            ];
        }
    }

    /**
     * Get client from billing system by external_id
     */
    public static function getBillingClient($clientId)
    {
        $externalId = 'bytez-' . $clientId;
        $url = 'http://127.0.0.1:8000/api/clients/' . urlencode($externalId);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Token: ' . self::BILLING_API_TOKEN
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

/**
 * API Endpoint to trigger sync from Bytez ERP
 * Add to your API routes (e.g., api/billing/sync.php)
 */
if (basename($_SERVER['PHP_SELF']) === 'billing-sync.php') {
    header('Content-Type: application/json');

    $method = $_SERVER['REQUEST_METHOD'];
    $db = getDB();

    if ($method === 'POST') {
        // Sync single client
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['client_id'])) {
            echo json_encode(['success' => false, 'message' => 'client_id required']);
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->bind_param('i', $input['client_id']);
        $stmt->execute();
        $client = $stmt->get_result()->fetch_assoc();

        if (!$client) {
            echo json_encode(['success' => false, 'message' => 'Client not found']);
            exit;
        }

        $result = BillingSyncController::syncClient($client);
        echo json_encode($result);

    } elseif ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'sync-all') {
        // Sync all clients
        $result = BillingSyncController::syncAllClients($db);
        echo json_encode([
            'success' => $result['failed'] === 0,
            'message' => "Synced {$result['synced']} clients",
            'details' => $result
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
}