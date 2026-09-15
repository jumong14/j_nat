<?php

require_once "config/database.php";
require_once "config/auth.php";

require_login();

$message = "";
$error = "";


// ========================================
// Validation Function
// ========================================

function validateMember(
    $memberCode,
    $name,
    $phone
) {

    $memberCode = trim($memberCode);
    $name = trim($name);
    $phone = trim($phone);


    // -------------------------------
    // รหัสสมาชิก
    // -------------------------------

    if ($memberCode === "") {

        return "กรุณากรอกรหัสสมาชิก";

    }


    // รหัสสมาชิกต้องเป็นตัวอักษร/ตัวเลข
    if (!preg_match('/^[A-Za-z0-9]+$/', $memberCode)) {

        return "รหัสสมาชิกต้องเป็นภาษาอังกฤษหรือตัวเลขเท่านั้น";

    }


    // -------------------------------
    // ชื่อ
    // -------------------------------

    if ($name === "") {

        return "กรุณากรอกชื่อ-นามสกุล";

    }


    // ชื่อต้องมีอย่างน้อย 2 ตัวอักษร
    if (mb_strlen($name) < 2) {

        return "ชื่อ-นามสกุลต้องมีอย่างน้อย 2 ตัวอักษร";

    }


    // -------------------------------
    // เบอร์โทร
    // -------------------------------

    if ($phone === "") {

        return "กรุณากรอกเบอร์โทร";

    }


    if (!preg_match('/^[0-9]+$/', $phone)) {

        return "เบอร์โทรต้องเป็นตัวเลขเท่านั้น";

    }


    if (strlen($phone) !== 10) {

        return "เบอร์โทรต้องมี 10 หลัก";

    }


    return "";

}


// ========================================
// ลบสมาชิก
// ========================================

if (isset($_GET["delete"])) {

    $id = (int)$_GET["delete"];


    if ($id <= 0) {

        $error = "รหัสสมาชิกไม่ถูกต้อง";

    } else {

        $result = supabaseRequest(
            "members",
            "DELETE",
            null,
            "?id=eq." . $id
        );


        if (
            $result["code"] >= 200 &&
            $result["code"] < 300
        ) {

            header(
                "Location: members.php?msg=deleted"
            );

            exit;

        } else {

            $error = "ลบสมาชิกไม่สำเร็จ";

        }

    }

}


// ========================================
// แก้ไขสมาชิก
// ========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["edit_id"])
) {

    $id =
        (int)($_POST["edit_id"] ?? 0);

    $memberCode =
        trim($_POST["member_code"] ?? "");

    $name =
        trim($_POST["name"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");


    // ====================================
    // Validation
    // ====================================

    $validationError =
        validateMember(
            $memberCode,
            $name,
            $phone
        );


    if ($validationError !== "") {

        $error = $validationError;

    } elseif ($id <= 0) {

        $error = "รหัสสมาชิกไม่ถูกต้อง";

    } else {


        // ====================================
        // ตรวจรหัสสมาชิกซ้ำ
        // ====================================

        $duplicateResult =
            supabaseRequest(
                "members",
                "GET",
                null,
                "?member_code=eq." .
                urlencode($memberCode) .
                "&id=neq." .
                $id .
                "&select=id"
            );


        $duplicateMembers =
            $duplicateResult["data"] ?? [];


        if (!empty($duplicateMembers)) {

            $error =
                "รหัสสมาชิกนี้มีอยู่แล้ว";

        } else {


            $data = [

                "member_code" =>
                    $memberCode,

                "name" =>
                    $name,

                "phone" =>
                    $phone

            ];


            $result =
                supabaseRequest(
                    "members",
                    "PATCH",
                    $data,
                    "?id=eq." . $id
                );


            if (
                $result["code"] >= 200 &&
                $result["code"] < 300
            ) {

                header(
                    "Location: members.php?msg=updated"
                );

                exit;

            } else {

                $error =
                    "แก้ไขสมาชิกไม่สำเร็จ";

            }

        }

    }

}


// ========================================
// เพิ่มสมาชิก
// ========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    !isset($_POST["edit_id"])
) {

    $memberCode =
        trim($_POST["member_code"] ?? "");

    $name =
        trim($_POST["name"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");


    // ====================================
    // Validation
    // ====================================

    $validationError =
        validateMember(
            $memberCode,
            $name,
            $phone
        );


    if ($validationError !== "") {

        $error = $validationError;

    } else {


        // ====================================
        // ตรวจรหัสสมาชิกซ้ำ
        // ====================================

        $duplicateResult =
            supabaseRequest(
                "members",
                "GET",
                null,
                "?member_code=eq." .
                urlencode($memberCode) .
                "&select=id"
            );


        $duplicateMembers =
            $duplicateResult["data"] ?? [];


        if (!empty($duplicateMembers)) {

            $error =
                "รหัสสมาชิกนี้มีอยู่แล้ว";

        } else {


            $data = [

                "member_code" =>
                    $memberCode,

                "name" =>
                    $name,

                "phone" =>
                    $phone

            ];


            $result =
                supabaseRequest(
                    "members",
                    "POST",
                    $data
                );


            if (
                $result["code"] >= 200 &&
                $result["code"] < 300
            ) {

                header(
                    "Location: members.php?msg=added"
                );

                exit;

            } else {

                $error =
                    "เพิ่มสมาชิกไม่สำเร็จ";

            }

        }

    }

}


// ========================================
// ข้อความแจ้งเตือน
// ========================================

if (isset($_GET["msg"])) {

    if ($_GET["msg"] === "added") {

        $message =
            "เพิ่มสมาชิกสำเร็จ";

    }

    if ($_GET["msg"] === "updated") {

        $message =
            "แก้ไขสมาชิกสำเร็จ";

    }

    if ($_GET["msg"] === "deleted") {

        $message =
            "ลบสมาชิกสำเร็จ";

    }

}


// ========================================
// โหมดแก้ไข
// ========================================

$editMember = null;

if (isset($_GET["edit"])) {

    $editId =
        (int)$_GET["edit"];


    $editResult =
        supabaseRequest(
            "members",
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

        $editMember =
            $editResult["data"][0];

    } else {

        $error =
            "ไม่พบสมาชิกที่ต้องการแก้ไข";

    }

}


// ========================================
// ค้นหา
// ========================================

$search =
    trim($_GET["search"] ?? "");


if ($search !== "") {

    $encodedSearch =
        urlencode("*" . $search . "*");


    $query =
        "?select=*" .
        "&or=(" .
        "member_code.ilike." .
        $encodedSearch .
        "," .
        "name.ilike." .
        $encodedSearch .
        "," .
        "phone.ilike." .
        $encodedSearch .
        ")" .
        "&order=id.desc";

} else {

    $query =
        "?select=*&order=id.desc";

}


// ========================================
// ดึงข้อมูลสมาชิก
// ========================================

$result =
    supabaseRequest(
        "members",
        "GET",
        null,
        $query
    );


$members = [];


if (
    $result["code"] >= 200 &&
    $result["code"] < 300
) {

    $members =
        $result["data"] ?? [];

} else {

    $error =
        "ไม่สามารถโหลดข้อมูลสมาชิกได้";

}


// ========================================
// Header
// ========================================

include "partials/header.php";

?>


<h1>จัดการสมาชิก</h1>


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
     แก้ไขสมาชิก
======================================== -->

<?php if ($editMember): ?>

<div class="form-box">

    <h2>แก้ไขสมาชิก</h2>


    <form method="post">

        <input
            type="hidden"
            name="edit_id"
            value="<?= (int)$editMember["id"] ?>"
        >


        <div class="grid2">


            <div>

                <label>
                    รหัสสมาชิก
                </label>

                <input
                    name="member_code"
                    value="<?= htmlspecialchars(
                        $editMember["member_code"] ?? ""
                    ) ?>"
                    pattern="[A-Za-z0-9]+"
                    title="ใช้ภาษาอังกฤษหรือตัวเลขเท่านั้น"
                    required
                >

            </div>


            <div>

                <label>
                    ชื่อ-นามสกุล
                </label>

                <input
                    name="name"
                    value="<?= htmlspecialchars(
                        $editMember["name"] ?? ""
                    ) ?>"
                    minlength="2"
                    required
                >

            </div>


            <div>

                <label>
                    เบอร์โทร
                </label>

                <input
                    name="phone"
                    value="<?= htmlspecialchars(
                        $editMember["phone"] ?? ""
                    ) ?>"
                    type="tel"
                    inputmode="numeric"
                    pattern="[0-9]{10}"
                    maxlength="10"
                    minlength="10"
                    placeholder="0812345678"
                    title="กรุณากรอกเบอร์โทร 10 หลัก"
                    required
                >

            </div>


        </div>


        <button
            class="btn primary"
            type="submit"
        >
            บันทึกการแก้ไข
        </button>


        <a
            class="btn secondary"
            href="members.php"
        >
            ยกเลิก
        </a>


    </form>

</div>

<?php endif; ?>



<!-- ========================================
     เพิ่มสมาชิก
======================================== -->

<?php if (!$editMember): ?>

<div class="form-box">

    <h2>เพิ่มสมาชิก</h2>


    <form
        method="post"
        onsubmit="return validateMemberForm(this)"
    >


        <div class="grid2">


            <div>

                <label>
                    รหัสสมาชิก
                </label>

                <input
                    name="member_code"
                    pattern="[A-Za-z0-9]+"
                    title="ใช้ภาษาอังกฤษหรือตัวเลขเท่านั้น"
                    placeholder="เช่น M001"
                    required
                >

            </div>


            <div>

                <label>
                    ชื่อ-นามสกุล
                </label>

                <input
                    name="name"
                    minlength="2"
                    placeholder="เช่น สมชาย ใจดี"
                    required
                >

            </div>


            <div>

                <label>
                    เบอร์โทร
                </label>

                <input
                    name="phone"
                    type="tel"
                    inputmode="numeric"
                    pattern="[0-9]{10}"
                    maxlength="10"
                    minlength="10"
                    placeholder="0812345678"
                    title="กรุณากรอกเบอร์โทร 10 หลัก"
                    required
                >

            </div>


        </div>


        <button
            class="btn primary"
            type="submit"
        >
            เพิ่มสมาชิก
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
        placeholder="ค้นหารหัส / ชื่อ / เบอร์โทร"
        value="<?= htmlspecialchars($search) ?>"
    >


    <button
        class="btn secondary"
        type="submit"
    >
        ค้นหา
    </button>

</form>



<!-- ========================================
     ตารางสมาชิก
======================================== -->

<div class="table-wrap">

<table class="table">


<tr>

    <th>รหัส</th>

    <th>ชื่อ</th>

    <th>เบอร์โทร</th>

    <th>วันที่สมัคร</th>

    <th>จัดการ</th>

</tr>


<?php foreach ($members as $m): ?>

<tr>


    <td>

        <?= htmlspecialchars(
            $m["member_code"] ?? ""
        ) ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $m["name"] ?? ""
        ) ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $m["phone"] ?? ""
        ) ?>

    </td>


    <td>

        <?= htmlspecialchars(
            $m["created_at"] ?? ""
        ) ?>

    </td>


    <td>


        <a
            class="btn secondary"
            href="members.php?edit=<?= (int)$m["id"] ?>"
        >
            แก้ไข
        </a>


        <a
            class="btn danger"
            onclick="return confirmDelete()"
            href="members.php?delete=<?= (int)$m["id"] ?>"
        >
            ลบ
        </a>


    </td>


</tr>

<?php endforeach; ?>


<?php if (empty($members)): ?>

<tr>

    <td
        colspan="5"
        style="text-align:center;"
    >

        ยังไม่มีสมาชิก

    </td>

</tr>

<?php endif; ?>


</table>

</div>



<script>

// ========================================
// Validate ฝั่งหน้าเว็บ
// ========================================

function validateMemberForm(form) {

    const memberCode =
        form.member_code.value.trim();

    const name =
        form.name.value.trim();

    const phone =
        form.phone.value.trim();


    if (memberCode === "") {

        alert("❌ กรุณากรอกรหัสสมาชิก");

        form.member_code.focus();

        return false;

    }


    if (!/^[A-Za-z0-9]+$/.test(memberCode)) {

        alert(
            "❌ รหัสสมาชิกต้องเป็นภาษาอังกฤษหรือตัวเลขเท่านั้น"
        );

        form.member_code.focus();

        return false;

    }


    if (name === "") {

        alert("❌ กรุณากรอกชื่อ-นามสกุล");

        form.name.focus();

        return false;

    }


    if (name.length < 2) {

        alert(
            "❌ ชื่อ-นามสกุลต้องมีอย่างน้อย 2 ตัวอักษร"
        );

        form.name.focus();

        return false;

    }


    if (phone === "") {

        alert("❌ กรุณากรอกเบอร์โทร");

        form.phone.focus();

        return false;

    }


    if (!/^[0-9]+$/.test(phone)) {

        alert(
            "❌ เบอร์โทรต้องเป็นตัวเลขเท่านั้น"
        );

        form.phone.focus();

        return false;

    }


    if (phone.length !== 10) {

        alert(
            "❌ เบอร์โทรต้องมี 10 หลัก"
        );

        form.phone.focus();

        return false;

    }


    return true;

}

</script>


<?php include "partials/footer.php"; ?>