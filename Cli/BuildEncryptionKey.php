<?php

if (php_sapi_name() != "cli") {
    echo 'This script has to be executed via CLI.' . "\n";
    exit(1);
}

if (! file_exists('vendor')) {
    echo 'This script has to be executed from the document root.' . "\n";
    exit(1);
}

// Generate a 256-bit encryption key
$password = openssl_random_pseudo_bytes(32);

echo "New encryption key is:\n";
echo base64_encode($password);
echo "\n";