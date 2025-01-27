<?php
$plain_password = "hisi";

$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Hashed Password: " . $hashed_password;
?>
