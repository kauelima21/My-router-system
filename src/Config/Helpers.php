<?php

function loadEnv(string $dir) {
    if (!file_exists("{$dir}/.env")) {
        return false;
    }

    $lines = file("{$dir}/.env");
    foreach ($lines as $line) {
        putenv(trim($line));
    }
}