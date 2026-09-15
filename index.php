<?php

require_once "config/database.php";

$result = supabaseRequest(
    "books",
    "GET",
    null,
    "?select=*"
);

echo "<h1>Library System</h1>";

echo "<h2>ทดสอบเชื่อมต่อ Supabase</h2>";

echo "<pre>";
print_r($result);
echo "</pre>";