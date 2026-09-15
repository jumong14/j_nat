<?php
require_once __DIR__ . "/../config/auth.php";
require_login();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Library Management</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<nav class="navbar">
    <a href="dashboard.php" class="brand">📚 Library</a>
    <div class="navlinks">
        <a href="dashboard.php">Dashboard</a>
        <a href="books.php">หนังสือ</a>
        <a href="members.php">สมาชิก</a>
        <a href="borrow.php">ยืม-คืน</a>
        <a href="scanner.php">สแกนบาร์โค้ด</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>
</nav>
<main class="container">
