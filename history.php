<?php

require_once "config/database.php";
require_once "config/auth.php";

require_login();


// ========================================
// รับค่าค้นหา / ตัวกรอง
// ========================================

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "all";


// ========================================
// ดึงข้อมูลการยืม
// ========================================

$borrowResult = supabaseRequest(
    "borrowings",
    "GET",
    null,
    "?select=*&order=id.desc"
);

$borrowings = [];

if (
    isset($borrowResult["code"]) &&
    $borrowResult["code"] >= 200 &&
    $borrowResult["code"] < 300
) {
    $borrowings = $borrowResult["data"] ?? [];
}


// ========================================
// ดึงสมาชิก
// ========================================

$memberResult = supabaseRequest(
    "members",
    "GET",
    null,
    "?select=*"
);

$members = [];

if (
    isset($memberResult["code"]) &&
    $memberResult["code"] >= 200 &&
    $memberResult["code"] < 300
) {
    $members = $memberResult["data"] ?? [];
}


// ========================================
// ดึงหนังสือ
// ========================================

$bookResult = supabaseRequest(
    "books",
    "GET",
    null,
    "?select=*"
);

$books = [];

if (
    isset($bookResult["code"]) &&
    $bookResult["code"] >= 200 &&
    $bookResult["code"] < 300
) {
    $books = $bookResult["data"] ?? [];
}


// ========================================
// สร้าง Map สมาชิก
// ========================================

$memberMap = [];

foreach ($members as $member) {

    if (isset($member["id"])) {

        $memberMap[$member["id"]] = $member;

    }

}


// ========================================
// สร้าง Map หนังสือ
// ========================================

$bookMap = [];

foreach ($books as $book) {

    if (isset($book["id"])) {

        $bookMap[$book["id"]] = $book;

    }

}


// ========================================
// รวมข้อมูล History
// ========================================

$history = [];

foreach ($borrowings as $borrowing) {

    $memberId = $borrowing["member_id"] ?? 0;
    $bookId = $borrowing["book_id"] ?? 0;


    $member = $memberMap[$memberId] ?? [];

    $book = $bookMap[$bookId] ?? [];


    $memberCode =
        $member["member_code"] ?? "-";

    $memberName =
        $member["name"] ?? "-";


    $bookTitle =
        $book["title"] ?? "-";

    $barcode =
        $book["barcode"] ?? "-";


    $borrowStatus =
        $borrowing["status"] ?? "";


    // ====================================
    // กรองสถานะ
    // ====================================

    if ($status === "borrowed") {

        if ($borrowStatus !== "borrowed") {
            continue;
        }

    }


    if ($status === "returned") {

        if ($borrowStatus !== "returned") {
            continue;
        }

    }


    // ====================================
    // ค้นหา
    // ====================================

    if ($search !== "") {

        $searchText = strtolower(
            $memberCode . " " .
            $memberName . " " .
            $bookTitle . " " .
            $barcode
        );

        if (
            strpos(
                $searchText,
                strtolower($search)
            ) === false
        ) {
            continue;
        }

    }


    // ====================================
    // เพิ่มข้อมูล
    // ====================================

    $history[] = [

        "member_code" =>
            $memberCode,

        "member_name" =>
            $memberName,

        "book_title" =>
            $bookTitle,

        "barcode" =>
            $barcode,

        "borrow_date" =>
            $borrowing["borrow_date"] ?? "-",

        "due_date" =>
            $borrowing["due_date"] ?? "-",

        "return_date" =>
            $borrowing["return_date"] ?? "-",

        "status" =>
            $borrowStatus

    ];

}


include "partials/header.php";

?>


<h1>🕘 ประวัติการยืม-คืน</h1>

<p>
    ดูประวัติการยืมและคืนหนังสือทั้งหมด
</p>


<hr>


<!-- ========================================
     ค้นหา / ตัวกรอง
======================================== -->

<form method="GET" style="margin-bottom:20px;">

    <input
        type="text"
        name="search"
        placeholder="ค้นหาสมาชิก / หนังสือ / Barcode"
        value="<?= htmlspecialchars($search) ?>"
        style="
            padding:8px;
            width:280px;
        "
    >


    <select
        name="status"
        style="padding:8px;"
    >

        <option
            value="all"
            <?= $status === "all" ? "selected" : "" ?>
        >
            ทั้งหมด
        </option>


        <option
            value="borrowed"
            <?= $status === "borrowed" ? "selected" : "" ?>
        >
            🟡 กำลังยืม
        </option>


        <option
            value="returned"
            <?= $status === "returned" ? "selected" : "" ?>
        >
            🟢 คืนแล้ว
        </option>

    </select>


    <button type="submit">
        🔎 ค้นหา
    </button>


    <a href="history.php">
        ล้างตัวกรอง
    </a>

</form>


<!-- ========================================
     ตารางประวัติ
======================================== -->

<div class="table-wrap">

<table class="table">

    <tr>

        <th>สมาชิก</th>

        <th>หนังสือ</th>

        <th>Barcode</th>

        <th>วันที่ยืม</th>

        <th>กำหนดคืน</th>

        <th>วันที่คืน</th>

        <th>สถานะ</th>

    </tr>


<?php if (empty($history)): ?>

    <tr>

        <td
            colspan="7"
            style="text-align:center;"
        >
            ยังไม่มีประวัติการยืม-คืน
        </td>

    </tr>


<?php else: ?>


<?php foreach ($history as $item): ?>

    <tr>


        <!-- สมาชิก -->

        <td>

            <strong>
                <?= htmlspecialchars(
                    $item["member_code"]
                ) ?>
            </strong>

            <br>

            <?= htmlspecialchars(
                $item["member_name"]
            ) ?>

        </td>


        <!-- หนังสือ -->

        <td>

            <?= htmlspecialchars(
                $item["book_title"]
            ) ?>

        </td>


        <!-- Barcode -->

        <td>

            <?= htmlspecialchars(
                $item["barcode"]
            ) ?>

        </td>


        <!-- วันที่ยืม -->

        <td>

            <?= htmlspecialchars(
                $item["borrow_date"]
            ) ?>

        </td>


        <!-- กำหนดคืน -->

        <td>

            <?= htmlspecialchars(
                $item["due_date"]
            ) ?>

        </td>


        <!-- วันที่คืน -->

        <td>

            <?php

            if (
                $item["return_date"] === "-" ||
                empty($item["return_date"])
            ) {

                echo "-";

            } else {

                echo htmlspecialchars(
                    $item["return_date"]
                );

            }

            ?>

        </td>


        <!-- สถานะ -->

        <td>

            <?php if (
                $item["status"] === "returned"
            ): ?>

                <span>
                    🟢 คืนแล้ว
                </span>


            <?php elseif (
                $item["status"] === "borrowed"
            ): ?>

                <span>
                    🟡 กำลังยืม
                </span>


            <?php else: ?>

                <?= htmlspecialchars(
                    $item["status"]
                ) ?>

            <?php endif; ?>

        </td>


    </tr>

<?php endforeach; ?>


<?php endif; ?>


</table>

</div>


<br>


<p>

    พบทั้งหมด

    <strong>
        <?= count($history) ?>
    </strong>

    รายการ

</p>


<?php include "partials/footer.php"; ?>