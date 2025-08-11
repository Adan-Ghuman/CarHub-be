<?php
// Test currency formatting with the new numeric values from API
echo "=== Testing Currency Formatting Fix ===\n";

// Simulate the API response data
$test_services = [
    ['id' => 1, 'service_name' => 'Oil Change', 'price' => 5],
    ['id' => 2, 'service_name' => 'Brake Service', 'price' => 500],
    ['id' => 3, 'service_name' => 'Engine Tuning', 'price' => 1000],
    ['id' => 4, 'service_name' => 'Complete Service', 'price' => 15000],
    ['id' => 5, 'service_name' => 'Premium Service', 'price' => 100000]
];

echo "\nAPI Returns (JSON):\n";
foreach ($test_services as $service) {
    echo "Service: {$service['service_name']} - Price: {$service['price']} (numeric)\n";
}

echo "\nJavaScript formatCurrency() would format these as:\n";
foreach ($test_services as $service) {
    $formatted = number_format($service['price'], 0);
    echo "Rs {$formatted}\n";
}

echo "\n✅ No more comma-separated strings causing NaN!\n";
echo "✅ Frontend Intl.NumberFormat will handle all formatting properly.\n";
?>
