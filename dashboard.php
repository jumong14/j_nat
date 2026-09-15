<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "config/database.php";
require_once "config/auth.php";

require_login();


// ========================================
// จำนวนหนังสือ
// ========================================

$booksResult = supabaseRequest(
    "books",
    "GET",
    null,
    "?select=id"
);

$books = 0;

if (
    isset($booksResult["code"]) &&
    $booksResult["code"] >= 200 &&
    $booksResult["code"] < 300
) {
    $books = count($booksResult["data"] ?? []);
}


// ========================================
// จำนวนสมาชิก
// ========================================

$membersResult = supabaseRequest(
    "members",
    "GET",
    null,
    "?select=id"
);

$members = 0;

if (
    isset($membersResult["code"]) &&
    $membersResult["code"] >= 200 &&
    $membersResult["code"] < 300
) {
    $members = count($membersResult["data"] ?? []);
}


// ========================================
// หนังสือว่าง
// ========================================

$availableResult = supabaseRequest(
    "books",
    "GET",
    null,
    "?select=available"
);

$available = 0;

if (
    isset($availableResult["code"]) &&
    $availableResult["code"] >= 200 &&
    $availableResult["code"] < 300
) {
    foreach ($availableResult["data"] ?? [] as $book) {
        $available += (int)($book["available"] ?? 0);
    }
}


// ========================================
// หนังสือที่กำลังถูกยืม
// ========================================

$borrowedResult = supabaseRequest(
    "borrowings",
    "GET",
    null,
    "?select=id&status=eq.borrowed"
);

$borrowed = 0;

if (
    isset($borrowedResult["code"]) &&
    $borrowedResult["code"] >= 200 &&
    $borrowedResult["code"] < 300
) {
    $borrowed = count($borrowedResult["data"] ?? []);
}


// ========================================
// รายการยืมล่าสุด
// ========================================

$latestResult = supabaseRequest(
    "borrowings",
    "GET",
    null,
    "?select=id,borrow_date,due_date,status&status=eq.borrowed&order=id.desc&limit=5"
);

$latest = [];

if (
    isset($latestResult["code"]) &&
    $latestResult["code"] >= 200 &&
    $latestResult["code"] < 300
) {
    $latest = $latestResult["data"] ?? [];
}


// ========================================
// Header
// ========================================

include "partials/header.php";

?>

<h1>Dashboard</h1>


<!-- ========================================
     เมนูระบบ
======================================== -->

<div style="
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:25px;
">

    <a href="books.php"
       style="
           padding:12px 18px;
           background:#eee;
           border-radius:8px;
           text-decoration:none;
       ">
        📚 หนังสือ
    </a>

    <a href="members.php"
       style="
           padding:12px 18px;
           background:#eee;
           border-radius:8px;
           text-decoration:none;
       ">
        👥 สมาชิก
    </a>

    <a href="borrow.php"
       style="
           padding:12px 18px;
           background:#eee;
           border-radius:8px;
           text-decoration:none;
       ">
        📖 ยืม / คืน
    </a>

    <a href="history.php"
       style="
           padding:12px 18px;
           background:#eee;
           border-radius:8px;
           text-decoration:none;
       ">
        🕘 ประวัติ
    </a>

</div>


<!-- ========================================
     Cards
======================================== -->

<div class="cards">

    <div class="card">
        <h3>หนังสือทั้งหมด</h3>

        <div class="number">
            <?= $books ?>
        </div>
    </div>


    <div class="card">
        <h3>สมาชิก</h3>

        <div class="number">
            <?= $members ?>
        </div>
    </div>


    <div class="card">
        <h3>หนังสือว่าง</h3>

        <div class="number">
            <?= $available ?>
        </div>
    </div>


    <div class="card">
        <h3>กำลังยืม</h3>

        <div class="number">
            <?= $borrowed ?>
        </div>
    </div>

</div>


<!-- ========================================
     รายการยืมล่าสุด
======================================== -->

<h2>รายการที่กำลังยืมล่าสุด</h2>


<div class="table-wrap">

<table class="table">

<tr>
    <th>ID</th>
    <th>วันที่ยืม</th>
    <th>กำหนดคืน</th>
    <th>สถานะ</th>
</tr>


<?php if (!empty($latest)): ?>

    <?php foreach ($latest as $r): ?>

        <tr>

            <td>
                <?= htmlspecialchars($r["id"] ?? "-") ?>
            </td>

            <td>
                <?= htmlspecialchars($r["borrow_date"] ?? "-") ?>
            </td>

            <td>
                <?= htmlspecialchars($r["due_date"] ?? "-") ?>
            </td>

            <td>
                <?= htmlspecialchars($r["status"] ?? "-") ?>
            </td>

        </tr>

    <?php endforeach; ?>

<?php else: ?>

    <tr>

        <td colspan="4" style="text-align:center;">
            ยังไม่มีรายการที่กำลังยืม
        </td>

    </tr>

<?php endif; ?>

</table>

</div>


<?php

include "partials/footer.php";

?>