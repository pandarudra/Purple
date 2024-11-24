<?php
include '../dbconnection.php';

// Define the admin email and the new password
$email = 'admin@example.com'; // Replace with the actual admin email
$newPassword = 'admin@123'; // Replace with the actual new password

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the admin password in the database
$sql = "UPDATE admins SET password='$hashedPassword' WHERE email='$email'";
if (mysqli_query($conn, $sql)) {
    echo "Password updated successfully.";
} else {
    echo "Error updating password: " . mysqli_error($conn);
}
?>