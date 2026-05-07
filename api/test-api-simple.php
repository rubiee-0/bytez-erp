<?php
/**
 * Simple API Test
 */

$api_url = 'http://127.0.0.1:8000/api/sync-clients';
$api_token = 'be9408a6f871f2afa2b741435728fd2cb815e65aa972e8414f2077e24bc50d23';

$client_data = [
    'external_id' => 'bytez-test-' . time(),
    'name' => 'Test Client API',
    'email' => 'test@example.com',
    'phone' => '555-1234',
    'address' => '123 Test St',
    'company' => 'Test Company'
];

echo "Testing API Endpoint: " . $api_url . "\n";
echo "API Token: " . substr($api_token, 0, 16) . "..." . substr($api_token, -16) . "\n";
echo "Data: " . json_encode($client_data) . "\n\n";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($client_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-API-Token: ' . $api_token,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

curl_close($ch);

echo "Response HTTP Code: " . $http_code . "\n";
echo "Response Body:\n";
echo $response . "\n\n";

if ($error) {
    echo "cURL Error: " . $error . "\n";
} else {
    echo "cURL: Success\n";
}

if ($http_code === 201) {
    echo "\n✅ API SYNC SUCCESSFUL!\n";
} elseif ($http_code === 422) {
    echo "\n⚠️  Validation Error (422) - Check response above\n";
} elseif ($http_code === 401) {
    echo "\n❌ Authentication Failed (401) - Check API token\n";
} else {
    echo "\n❌ Request Failed - HTTP " . $http_code . "\n";
}
?>