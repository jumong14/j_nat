<?php

require_once "config/database.php";
require_once "config/auth.php";

require_login();

$message = "";
$error = "";


// ========================================
// ลบหนังสือ
// ========================================

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $result = supabaseRequest(
        "books",
        "DELETE",
        null,
        "?id=eq." . $id
    );

    if ($result["code"] >= 200 && $result["code"] < 300) {

        header("Location: books.php?msg=deleted");
        exit;

    } else {

        $error = "ลบหนังสือไม่สำเร็จ";
    }
}


// ========================================
// แก้ไขหนังสือ
// ========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["edit_id"])
) {

    $id = (int)$_POST["edit_id"];

    $barcode = trim($_POST["barcode"] ?? "");
    $title = trim($_POST["title"] ?? "");
    $author = trim($_POST["author"] ?? "");
    $category = trim($_POST["category"] ?? "");

    $quantity = max(
        1,
        (int)($_POST["quantity"] ?? 1)
    );

    $available = max(
        0,
        (int)($_POST["available"] ?? 0)
    );


    if ($barcode === "" || $title === "") {

        $error = "กรุณากรอก Barcode และชื่อหนังสือ";

    } elseif ($available > $quantity) {

        $error = "จำนวนคงเหลือห้ามมากกว่าจำนวนทั้งหมด";

    } else {

        $data = [
            "barcode" => $barcode,
            "title" => $title,
            "author" => $author,
            "category" => $category,
            "quantity" => $quantity,
            "available" => $available
        ];


        $result = supabaseRequest(
            "books",
            "PATCH",
            $data,
            "?id=eq." . $id
        );


        if (
            $result["code"] >= 200 &&
            $result["code"] < 300
        ) {

            header("Location: books.php?msg=updated");
            exit;

        } else {

            $error =
                "แก้ไขหนังสือไม่สำเร็จ: " .
                "Barcode อาจซ้ำ";
        }
    }
}


// ========================================
// เพิ่มหนังสือ
// ========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    !isset($_POST["edit_id"])
) {

    $barcode = trim($_POST["barcode"] ?? "");
    $title = trim($_POST["title"] ?? "");
    $author = trim($_POST["author"] ?? "");
    $category = trim($_POST["category"] ?? "");

    $quantity = max(
        1,
        (int)($_POST["quantity"] ?? 1)
    );


    if ($barcode === "" || $title === "") {

        $error = "กรุณากรอก Barcode และชื่อหนังสือ";

    } else {

        $data = [
            "barcode" => $barcode,
            "title" => $title,
            "author" => $author,
            "category" => $category,
            "quantity" => $quantity,
            "available" => $quantity
        ];


        $result = supabaseRequest(
            "books",
            "POST",
            $data
        );


        if (
            $result["code"] >= 200 &&
            $result["code"] < 300
        ) {

            header("Location: books.php?msg=added");
            exit;

        } else {

            $error =
                "เพิ่มไม่สำเร็จ: " .
                "Barcode อาจซ้ำ";
        }
    }
}


// ========================================
// ข้อความแจ้งเตือน
// ========================================

if (isset($_GET["msg"])) {

    if ($_GET["msg"] === "added") {
        $message = "เพิ่มหนังสือสำเร็จ";
    }

    if ($_GET["msg"] === "updated") {
        $message = "แก้ไขหนังสือสำเร็จ";
    }

    if ($_GET["msg"] === "deleted") {
        $message = "ลบหนังสือสำเร็จ";
    }
}


// ========================================
// โหมดแก้ไข
// ========================================

$editBook = null;

if (isset($_GET["edit"])) {

    $editId = (int)$_GET["edit"];

    $editResult = supabaseRequest(
        "books",
        "GET",
        null,
        "?id=eq." .
        $editId .
        "&select=*"
    );


    if (
        $editResult["code"] >= 200 &&
        $editResult["code"] < 300 &&
        !empty($editResult["data"])
    ) {

        $editBook = $editResult["data"][0];

    } else {

        $error = "ไม่พบหนังสือที่ต้องการแก้ไข";
    }
}


// ========================================
// ค้นหา
// ========================================

$search = trim($_GET["search"] ?? "");


if ($search !== "") {

    $encodedSearch = urlencode("*" . $search . "*");

    $query =
        "?select=*" .
        "&or=(" .
        "barcode.ilike." .
        $encodedSearch .
        "," .
        "title.ilike." .
        $encodedSearch .
        "," .
        "author.ilike." .
        $encodedSearch .
        ")" .
        "&order=id.desc";

} else {

    $query = "?select=*&order=id.desc";
}


// ========================================
// ดึงข้อมูลหนังสือ
// ========================================

$result = supabaseRequest(
    "books",
    "GET",
    null,
    $query
);


$books = [];


if (
    $result["code"] >= 200 &&
    $result["code"] < 300
) {

    $books = $result["data"] ?? [];

} else {

    $error = "ไม่สามารถโหลดข้อมูลหนังสือได้";
}


// ========================================
// Header
// ========================================

include "partials/header.php";

?>


<h1>📚 จัดการหนังสือ</h1>


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



<!-- ========================================
     แก้ไขหนังสือ
======================================== -->

<?php if ($editBook): ?>

<div class="form-box">

    <h2>✏️ แก้ไขหนังสือ</h2>


    <form method="post">

        <input
            type="hidden"
            name="edit_id"
            value="<?= (int)$editBook["id"] ?>"
        >


        <div class="grid2">

            <div>

                <label>Barcode</label>

                <input
                    name="barcode"
                    value="<?= htmlspecialchars(
                        $editBook["barcode"] ?? ""
                    ) ?>"
                    placeholder="เช่น LIB00001"
                    required
                >

            </div>


            <div>

                <label>ชื่อหนังสือ</label>

                <input
                    name="title"
                    value="<?= htmlspecialchars(
                        $editBook["title"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <div>

                <label>ผู้แต่ง</label>

                <input
                    name="author"
                    value="<?= htmlspecialchars(
                        $editBook["author"] ?? ""
                    ) ?>"
                >

            </div>


            <div>

                <label>หมวดหมู่</label>

                <input
                    name="category"
                    value="<?= htmlspecialchars(
                        $editBook["category"] ?? ""
                    ) ?>"
                >

            </div>


            <div>

                <label>จำนวนทั้งหมด</label>

                <input
                    type="number"
                    name="quantity"
                    min="1"
                    value="<?= (int)(
                        $editBook["quantity"] ?? 1
                    ) ?>"
                    required
                >

            </div>


            <div>

                <label>จำนวนคงเหลือ</label>

                <input
                    type="number"
                    name="available"
                    min="0"
                    value="<?= (int)(
                        $editBook["available"] ?? 0
                    ) ?>"
                    required
                >

            </div>

        </div>


        <button
            class="btn primary"
            type="submit"
        >
            💾 บันทึกการแก้ไข
        </button>


        <a
            class="btn secondary"
            href="books.php"
        >
            ยกเลิก
        </a>

    </form>

</div>

<?php endif; ?>



<!-- ========================================
     เพิ่มหนังสือ
======================================== -->

<?php if (!$editBook): ?>

<div class="form-box">

    <h2>➕ เพิ่มหนังสือ</h2>


    <form method="post">

        <div class="grid2">

            <div>

                <label>Barcode</label>

                <input
                    name="barcode"
                    placeholder="เช่น LIB00003"
                    required
                >

                <small>
                    ใช้ Barcode ที่ไม่ซ้ำกับหนังสือเล่มอื่น
                </small>

            </div>


            <div>

                <label>ชื่อหนังสือ</label>

                <input
                    name="title"
                    required
                >

            </div>


            <div>

                <label>ผู้แต่ง</label>

                <input
                    name="author"
                >

            </div>


            <div>

                <label>หมวดหมู่</label>

                <input
                    name="category"
                >

            </div>


            <div>

                <label>จำนวน</label>

                <input
                    type="number"
                    name="quantity"
                    min="1"
                    value="1"
                    required
                >

            </div>

        </div>


        <button
            class="btn primary"
            type="submit"
        >
            ➕ เพิ่มหนังสือ
        </button>

    </form>

</div>

<?php endif; ?>



<!-- ========================================
     ค้นหา
======================================== -->

<form
    class="toolbar"
    method="get"
>

    <input
        name="search"
        placeholder="ค้นหาชื่อ / Barcode / ผู้แต่ง"
        value="<?= htmlspecialchars($search) ?>"
    >


    <button
        class="btn secondary"
        type="submit"
    >
        🔍 ค้นหา
    </button>


    <?php if ($search !== ""): ?>

    <a
        class="btn secondary"
        href="books.php"
    >
        ล้างค้นหา
    </a>

    <?php endif; ?>

</form>



<!-- ========================================
     ตารางหนังสือ
======================================== -->

<div class="table-wrap">

<table class="table">

<tr>

    <th>Barcode</th>
    <th>ชื่อ</th>
    <th>ผู้แต่ง</th>
    <th>หมวด</th>
    <th>คงเหลือ</th>
    <th>จัดการ</th>

</tr>


<?php foreach ($books as $b): ?>

<tr>

    <!-- Barcode -->

    <td>

        <strong>
            <?= htmlspecialchars(
                $b["barcode"] ?? ""
            ) ?>
        </strong>

    </td>


    <!-- ชื่อ -->

    <td>

        <?= htmlspecialchars(
            $b["title"] ?? ""
        ) ?>

    </td>


    <!-- ผู้แต่ง -->

    <td>

        <?= htmlspecialchars(
            $b["author"] ?? ""
        ) ?>

    </td>


    <!-- หมวด -->

    <td>

        <?= htmlspecialchars(
            $b["category"] ?? ""
        ) ?>

    </td>


    <!-- จำนวน -->

    <td>

        <?= (int)(
            $b["available"] ?? 0
        ) ?>

        /

        <?= (int)(
            $b["quantity"] ?? 0
        ) ?>

    </td>


    <!-- จัดการ -->

    <td>

        <?php if (
            !empty($b["barcode"]) &&
            (int)($b["available"] ?? 0) > 0
        ): ?>

        <a
            class="btn primary"
            href="borrow.php?target=borrow&barcode=<?= urlencode($b["barcode"]) ?>"
        >
            📖 ยืม
        </a>

        <?php else: ?>

        <button
            class="btn secondary"
            type="button"
            disabled
        >
            📕 ไม่มีเล่มว่าง
        </button>

        <?php endif; ?>


        <a
            class="btn secondary"
            href="books.php?edit=<?= (int)$b["id"] ?>"
        >
            ✏️ แก้ไข
        </a>


        <?php if (!empty($b["barcode"])): ?>

        <a
            class="btn primary"
            target="_blank"
            href="barcode.php?id=<?= (int)$b["id"] ?>"
        >
            🏷️ Barcode
        </a>

        <?php endif; ?>


        <a
            class="btn danger"
            onclick="return confirmDelete()"
            href="books.php?delete=<?= (int)$b["id"] ?>"
        >
            🗑️ ลบ
        </a>

    </td>

</tr>

<?php endforeach; ?>


<?php if (empty($books)): ?>

<tr>

    <td
        colspan="6"
        style="text-align:center;"
    >

        ไม่พบหนังสือ

    </td>

</tr>

<?php endif; ?>


</table>

</div>


<?php include "partials/footer.php"; ?>
```
