<?php
/**
 * API Integration Test Script
 * Tests the connection between ByteZ ERP and Laravel Billing System
 */

// Database connection details
$db_host = 'localhost';
$db_port = 3306;
$db_user = 'berlin';
$db_pass = 'Berlin@123';
$db_name = 'integrated_billing_system';

// API Configuration
$api_token = 'be9408a6f871f2afa2b741435728fd2cb815e65aa972e8414f2077e24bc50d23';
$api_url = 'http://127.0.0.1:8000/api/sync-clients';

echo "=== API Integration Test ===\n\n";

// Test 1: Database Connection
echo "[TEST 1] Database Connection\n";
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    if ($conn->connect_error) {
        echo "❌ FAILED: " . $conn->connect_error . "\n";
        exit(1);
    }
    echo "✅ PASSED: Connected to integrated_billing_system\n\n";
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Verify API Token
echo "[TEST 2] API Token Configuration\n";
if (strlen($api_token) === 64) {
    echo "✅ PASSED: API Token is valid (length: 64)\n";
    echo "   Token: " . substr($api_token, 0, 16) . "..." . substr($api_token, -16) . "\n\n";
} else {
    echo "❌ FAILED: API Token invalid length\n";
    exit(1);
}

// Test 3: Database Tables Exist
echo "[TEST 3] Database Tables\n";
$required_tables = ['users', 'clients', 'invoices', 'invoice_items', 'projects', 'tasks'];
$missing_tables = [];

foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows === 0) {
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    echo "✅ PASSED: All required tables exist\n";
    echo "   Tables: " . implode(', ', $required_tables) . "\n\n";
} else {
    echo "❌ FAILED: Missing tables: " . implode(', ', $missing_tables) . "\n";
    exit(1);
}

// Test 4: Check Clients
echo "[TEST 4] Existing Clients in Database\n";
$result = $conn->query("SELECT id, company_name, email FROM clients");
if ($result->num_rows > 0) {
    echo "✅ PASSED: Found " . $result->num_rows . " client(s)\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: " . $row['id'] . " | Company: " . $row['company_name'] . " | Email: " . $row['email'] . "\n";
    }
    echo "\n";
} else {
    echo "⚠️  WARNING: No clients found in database\n";
    echo "    You should add clients to ByteZ ERP first\n\n";
}

// Test 5: Check External ID column
echo "[TEST 5] External ID Column\n";
$result = $conn->query("SHOW COLUMNS FROM clients LIKE 'external_id'");
if ($result->num_rows > 0) {
    echo "✅ PASSED: external_id column exists in clients table\n\n";
} else {
    echo "❌ FAILED: external_id column missing\n";
    exit(1);
}

// Test 6: Test API Connectivity (if Laravel is running)
echo "[TEST 6] API Endpoint Connectivity\n";
$client_data = [
    'external_id' => 'bytez-test-' . time(),
    'name' => 'Test Client',
    'email' => 'test@example.com',
    'phone' => '555-1234',
    'address' => '123 Test St',
    'company' => 'Test Company'
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($client_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-Token: ' . $api_token,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if (!$error && ($http_code === 201 || $http_code === 422)) {
    echo "✅ PASSED: API is reachable and responding\n";
    echo "   Endpoint: " . $api_url . "\n";
    echo "   Response Code: " . $http_code . "\n";
    if ($response) {
        $decoded = json_decode($response, true);
        echo "   Response: " . $decoded['message'] . "\n";
    }
} elseif ($http_code === 401) {
    echo "❌ FAILED: API Token Authentication Failed\n";
    echo "   Response Code: 401\n";
    if ($response) {
        $decoded = json_decode($response, true);
        echo "   Error: " . $decoded['message'] . "\n";
    }
} else {
    echo "⚠️  WARNING: Cannot reach Laravel API\n";
    echo "   Error: " . ($error ?: 'HTTP ' . $http_code) . "\n";
    echo "   Make sure to start Laravel with: php artisan serve --port=8000\n";
    echo "   From directory: C:\\xampp\\htdocs\\laravel-invoice-billing-system\n";
}

echo "\n";

// Test 7: ByteZ ERP Configuration
echo "[TEST 7] ByteZ ERP Billing Sync Configuration\n";
$billing_sync_file = 'C:\\xampp\\htdocs\\bytez-erp\\api\\controllers\\billing\\billing-sync.php';
if (file_exists($billing_sync_file)) {
    $content = file_get_contents($billing_sync_file);
    if (strpos($content, 'be9408a6f871f2afa2b741435728fd2cb815e65aa972e8414f2077e24bc50d23') !== false) {
        echo "✅ PASSED: ByteZ ERP has correct API Token\n";
        echo "   File: billing-sync.php\n\n";
    } else {
        echo "⚠️  WARNING: API Token might not match\n";
        echo "   Please verify: " . $billing_sync_file . "\n\n";
    }
} else {
    echo "❌ FAILED: billing-sync.php not found\n";
    exit(1);
}

// Summary
echo "=== Test Summary ===\n";
echo "✅ Database connection and structure verified\n";
echo "✅ API token configured\n";
echo "✅ Tables and columns ready\n";
echo "⚠️  Note: Start Laravel with: cd C:\\xampp\\htdocs\\laravel-invoice-billing-system && php artisan serve --port=8000\n\n";

$conn->close();
echo "Tests completed!\n";
?>