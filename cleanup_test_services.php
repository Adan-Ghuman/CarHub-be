<?php
include "config.php";

// Clean up test services
$deleteQuery = "DELETE FROM workshop_services WHERE service_name LIKE '%Test%' OR service_name LIKE '%API Test%'";
$result = mysqli_query($conn, $deleteQuery);

if ($result) {
    echo "✅ Test services cleaned up successfully\n";
    echo "Deleted " . mysqli_affected_rows($conn) . " test services\n";
} else {
    echo "❌ Failed to clean up test services: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
