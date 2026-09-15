<?php

require_once "config/database.php";
require_once "config/auth.php";

require_login();

$message = "";
$error = "";


// ========================================
// รับ Barcode
// ========================================

$scannedBarcode = trim($_GET["barcode"] ?? "");
$scannedTarget = $_GET["target"] ?? "";


// ========================================
// ยืม / คืน
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    // ====================================
    // ยืม
    // ====================================

    if ($action === "borrow") {

        $member_code = trim($_POST["member_code"] ?? "");
        $barcode = trim($_POST["barcode"] ?? "");


        $memberResult = supabaseRequest(
            "members",
            "GET",
            null,
            "?member_code=eq." .
            urlencode($member_code) .
            "&select=*"
        );

        $members = $memberResult["data"] ?? [];
        $member = $members[0] ?? null;


        $bookResult = supabaseRequest(
            "books",
            "GET",
            null,
            "?barcode=eq." .
            urlencode($barcode) .
            "&select=*"
        );

        $books = $bookResult["data"] ?? [];
        $book = $books[0] ?? null;


        if (!$member) {

            $error = "ไม่พบสมาชิก";

        } elseif (!$book) {

            $error = "ไม่พบหนังสือ";

        } elseif ((int)$book["available"] <= 0) {

            $error = "หนังสือเล่มนี้ไม่มีคงเหลือ";

        } else {

            $borrowDate = date("Y-m-d");

            $dueDate = date(
                "Y-m-d",
                strtotime("+7 days")
            );


            $borrowData = [

                "member_id" =>
                    (int)$member["id"],

                "book_id" =>
                    (int)$book["id"],

                "borrow_date" =>
                    $borrowDate,

                "due_date" =>
                    $dueDate,

                "status" =>
                    "borrowed"

            ];


            $borrowResult = supabaseRequest(
                "borrowings",
                "POST",
                $borrowData
            );


            if (
                $borrowResult["code"] >= 200 &&
                $borrowResult["code"] < 300
            ) {

                $newAvailable =
                    (int)$book["available"] - 1;


                $updateResult = supabaseRequest(
                    "books",
                    "PATCH",
                    [
                        "available" =>
                            $newAvailable
                    ],
                    "?id=eq." .
                    (int)$book["id"]
                );


                if (
                    $updateResult["code"] >= 200 &&
                    $updateResult["code"] < 300
                ) {

                    $message =
                        "ยืมหนังสือสำเร็จ: " .
                        $book["title"];

                } else {

                    $error =
                        "บันทึกจำนวนหนังสือไม่สำเร็จ";

                }

            } else {

                $error =
                    "บันทึกรายการยืมไม่สำเร็จ";

            }

        }

    }



    // ====================================
    // คืน
    // ====================================

    if ($action === "return") {

        $barcode = trim($_POST["barcode"] ?? "");


        $bookResult = supabaseRequest(
            "books",
            "GET",
            null,
            "?barcode=eq." .
            urlencode($barcode) .
            "&select=*"
        );

        $books = $bookResult["data"] ?? [];
        $book = $books[0] ?? null;


        if (!$book) {

            $error = "ไม่พบหนังสือ";

        } else {

            $borrowResult = supabaseRequest(
                "borrowings",
                "GET",
                null,
                "?book_id=eq." .
                (int)$book["id"] .
                "&status=eq.borrowed" .
                "&select=*" .
                "&order=id.desc" .
                "&limit=1"
            );

            $borrowings =
                $borrowResult["data"] ?? [];

            $borrowing =
                $borrowings[0] ?? null;


            if (!$borrowing) {

                $error =
                    "ไม่พบรายการยืมของ Barcode นี้";

            } else {

                $returnResult = supabaseRequest(
                    "borrowings",
                    "PATCH",
                    [
                        "status" => "returned",
                        "return_date" => date("Y-m-d")
                    ],
                    "?id=eq." .
                    (int)$borrowing["id"]
                );


                if (
                    $returnResult["code"] >= 200 &&
                    $returnResult["code"] < 300
                ) {

                    $newAvailable =
                        (int)$book["available"] + 1;


                    $updateResult = supabaseRequest(
                        "books",
                        "PATCH",
                        [
                            "available" =>
                                $newAvailable
                        ],
                        "?id=eq." .
                        (int)$book["id"]
                    );


                    if (
                        $updateResult["code"] >= 200 &&
                        $updateResult["code"] < 300
                    ) {

                        $message =
                            "คืนหนังสือสำเร็จ: " .
                            $book["title"];

                    } else {

                        $error =
                            "คืนสำเร็จแต่ไม่สามารถอัปเดตจำนวนหนังสือได้";

                    }

                } else {

                    $error =
                        "อัปเดตรายการคืนไม่สำเร็จ";

                }

            }

        }

    }

}


// ========================================
// รายการที่กำลังยืม
// ========================================

$activeResult = supabaseRequest(
    "borrowings",
    "GET",
    null,
    "?status=eq.borrowed&select=*&order=id.desc"
);

$activeBorrowings =
    $activeResult["data"] ?? [];

$active = [];


foreach ($activeBorrowings as $borrowing) {

    $memberResult = supabaseRequest(
        "members",
        "GET",
        null,
        "?id=eq." .
        (int)$borrowing["member_id"] .
        "&select=*"
    );

    $members =
        $memberResult["data"] ?? [];

    $member =
        $members[0] ?? [];


    $bookResult = supabaseRequest(
        "books",
        "GET",
        null,
        "?id=eq." .
        (int)$borrowing["book_id"] .
        "&select=*"
    );

    $books =
        $bookResult["data"] ?? [];

    $book =
        $books[0] ?? [];


    $active[] = [

        "member_code" =>
            $member["member_code"] ?? "",

        "name" =>
            $member["name"] ?? "",

        "barcode" =>
            $book["barcode"] ?? "",

        "title" =>
            $book["title"] ?? "",

        "borrow_date" =>
            $borrowing["borrow_date"] ?? "",

        "due_date" =>
            $borrowing["due_date"] ?? ""

    ];

}


include "partials/header.php";

?>


<h1>ระบบยืม-คืนหนังสือ</h1>


<?php if ($message): ?>

<div class="alert success-msg">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="alert error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<div class="grid2">


    <!-- =================================
         ยืมหนังสือ
    ================================== -->

    <div class="form-box">

        <h2>📕 ยืมหนังสือ</h2>


        <form method="post">

            <input
                type="hidden"
                name="action"
                value="borrow"
            >


            <label>
                รหัสสมาชิก
            </label>

            <input
                id="member_code"
                name="member_code"
                placeholder="เช่น M001"
                required
            >


            <label>
                Barcode หนังสือ
            </label>

            <input
                id="borrow_barcode"
                name="barcode"
                value="<?= htmlspecialchars($scannedBarcode) ?>"
                placeholder="เช่น LIB00001"
                autocomplete="off"
                required
            >


            <!-- ชื่อหนังสือ -->
            <div
                id="book_name"
                style="
                    margin-top:10px;
                    padding:10px;
                    background:#f5f5f5;
                    border-radius:8px;
                    display:none;
                    font-weight:bold;
                "
            ></div>


            <button
                class="btn primary"
                type="submit"
            >
                ยืนยันการยืม
            </button>


            <a
                class="btn secondary"
                href="scanner.php?target=borrow"
            >
                📷 สแกน Barcode
            </a>

        </form>

    </div>



    <!-- =================================
         คืนหนังสือ
    ================================== -->

    <div class="form-box">

        <h2>📗 คืนหนังสือ</h2>


        <form method="post">

            <input
                type="hidden"
                name="action"
                value="return"
            >


            <label>
                Barcode หนังสือ
            </label>

            <input
                id="return_barcode"
                name="barcode"
                value="<?=
                    $scannedTarget === "return"
                        ? htmlspecialchars($scannedBarcode)
                        : ""
                ?>"
                placeholder="เช่น LIB00001"
                autocomplete="off"
                required
            >


            <button
                class="btn success"
                type="submit"
            >
                ยืนยันการคืน
            </button>


            <a
                class="btn secondary"
                href="scanner.php?target=return"
            >
                📷 สแกน Barcode
            </a>

        </form>

    </div>

</div>



<!-- =====================================
     รายการที่กำลังยืม
====================================== -->

<h2>รายการที่กำลังยืม</h2>


<div class="table-wrap">

<table class="table">

<tr>

    <th>สมาชิก</th>
    <th>หนังสือ</th>
    <th>Barcode</th>
    <th>ยืม</th>
    <th>กำหนดคืน</th>

</tr>


<?php foreach ($active as $r): ?>

<tr>

    <td>
        <?= htmlspecialchars(
            $r["member_code"] .
            " - " .
            $r["name"]
        ) ?>
    </td>


    <td>
        <?= htmlspecialchars(
            $r["title"]
        ) ?>
    </td>


    <td>
        <?= htmlspecialchars(
            $r["barcode"]
        ) ?>
    </td>


    <td>
        <?= htmlspecialchars(
            $r["borrow_date"]
        ) ?>
    </td>


    <td>
        <?= htmlspecialchars(
            $r["due_date"]
        ) ?>
    </td>

</tr>

<?php endforeach; ?>


<?php if (empty($active)): ?>

<tr>

    <td
        colspan="5"
        style="text-align:center;"
    >
        ไม่มีรายการที่กำลังยืม
    </td>

</tr>

<?php endif; ?>


</table>

</div>



<script>

// ========================================
// ค้นหาชื่อหนังสือจาก Barcode
// ========================================

const barcodeInput =
    document.getElementById("borrow_barcode");

const bookName =
    document.getElementById("book_name");


// ========================================
// ค้นหา
// ========================================

function findBook() {

    const barcode =
        barcodeInput.value.trim();


    if (barcode === "") {

        bookName.style.display = "none";

        return;
    }


    fetch(
        "books.php?search=" +
        encodeURIComponent(barcode)
    )

    .then(response => response.text())

    .then(html => {

        // ดึงชื่อหนังสือจากหน้า books.php
        const temp =
            document.createElement("div");

        temp.innerHTML = html;


        const rows =
            temp.querySelectorAll("table tbody tr");


        let foundTitle = "";


        rows.forEach(row => {

            const cells =
                row.querySelectorAll("td");


            if (cells.length >= 5) {

                const barcodeText =
                    cells[0].textContent.trim();


                if (
                    barcodeText === barcode
                ) {

                    foundTitle =
                        cells[1].textContent.trim();

                }

            }

        });


        if (foundTitle !== "") {

            bookName.textContent =
                "📖 " + foundTitle;

            bookName.style.display =
                "block";

        } else {

            bookName.textContent =
                "❌ ไม่พบหนังสือ";

            bookName.style.display =
                "block";

        }

    })

    .catch(error => {

        console.error(error);

        bookName.textContent =
            "❌ ไม่สามารถค้นหาหนังสือได้";

        bookName.style.display =
            "block";

    });

}


// ========================================
// ตอนพิมพ์ Barcode
// ========================================

barcodeInput.addEventListener(
    "input",
    function () {

        clearTimeout(
            window.bookTimer
        );

        window.bookTimer =
            setTimeout(
                findBook,
                300
            );

    }
);


// ========================================
// ตอนสแกน / ออกจากช่อง
// ========================================

barcodeInput.addEventListener(
    "change",
    findBook
);

barcodeInput.addEventListener(
    "blur",
    findBook
);


// ========================================
// ถ้ามี Barcode มาจากหน้า Books
// ========================================

if (
    barcodeInput.value.trim() !== ""
) {

    findBook();

}

</script>


<?php include "partials/footer.php"; ?>