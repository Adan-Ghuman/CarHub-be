<?php
include "config.php";

$result = mysqli_query($conn, "DELETE FROM workshop_services WHERE id = 14");
if ($result) {
    echo "✅ Test service removed\n";
} else {
    echo "❌ Failed to remove test service\n";
}
mysqli_close($conn);
?>
