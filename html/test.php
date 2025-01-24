<?php
// The plain-text password
$plain_password = "hisi";

// Hash the password
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Output the hashed password
echo "Hashed Password: " . $hashed_password;
?>
