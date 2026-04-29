<?php
require_once 'api/server/koneksi.php';
$query = "UPDATE users SET role = 'staff' WHERE role = 'user'";
if (mysqli_query($koneksi, $query)) {
    echo "Updated " . mysqli_affected_rows($koneksi) . " users from 'user' to 'staff'.\n";
} else {
    echo "Error: " . mysqli_error($koneksi) . "\n";
}
?>
