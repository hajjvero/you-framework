<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing env(): " . (function_exists('env') ? 'OK' : 'FAIL') . "\n";
echo "Testing base_path(): " . (function_exists('base_path') ? 'OK' : 'FAIL') . "\n";
echo "Testing discover_files(): " . (function_exists('discover_files') ? 'OK' : 'FAIL') . "\n";
