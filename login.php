<?php

require_once "config/database.php";

session_start();


// ========================================
// ถ้า Login อยู่แล้ว
// ========================================

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}


$error = "";


// ========================================
// Login
// ========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";


    if ($username === "" || $password === "") {

        $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";

    } else {

        // ========================================
        // ค้นหา User จาก Supabase
        // ========================================

        $query = "?username=eq." . urlencode($username) . "&select=*";

        $result = supabaseRequest(
            "users",
            "GET",
            null,
            $query
        );


        // ========================================
        // ตรวจสอบ HTTP Response
        // ========================================

        $httpCode = $result["code"] ?? 0;


        if ($httpCode !== 200) {

            $supabaseError = "";

            if (isset($result["data"]["message"])) {
                $supabaseError = $result["data"]["message"];
            } elseif (isset($result["data"]["error"])) {
                $supabaseError = $result["data"]["error"];
            } elseif (isset($result["data"]["hint"])) {
                $supabaseError = $result["data"]["hint"];
            }

            $error = "เชื่อมต่อ Supabase ไม่สำเร็จ";

            if ($supabaseError !== "") {
                $error .= " : " . $supabaseError;
            }

            $error .= " (HTTP " . $httpCode . ")";

        } else {

            // ========================================
            // รับข้อมูล User
            // ========================================

            $users = $result["data"] ?? [];


            if (!is_array($users)) {
                $users = [];
            }


            $user = $users[0] ?? null;


            // ========================================
            // ไม่พบ Username
            // ========================================

            if (!$user) {

                $error = "ไม่พบชื่อผู้ใช้ '" .
                    htmlspecialchars($username, ENT_QUOTES, "UTF-8") .
                    "' ในฐานข้อมูล";


            } else {

                // ========================================
                // ตรวจสอบ Password
                // ========================================

                $inputPassword = hash("sha256", $password);

                $databasePassword = (string)($user["password"] ?? "");


                if (
                    $databasePassword !== "" &&
                    hash_equals(
                        $databasePassword,
                        $inputPassword
                    )
                ) {

                    // ========================================
                    // Login สำเร็จ
                    // ========================================

                    $_SESSION["user"] = [
                        "id" => $user["id"],
                        "username" => $user["username"]
                    ];


                    header("Location: dashboard.php");
                    exit;


                } else {

                    $error = "พบผู้ใช้แล้ว แต่รหัสผ่านไม่ตรงกัน";
                }
            }
        }
    }
}

?>
<!doctype html>

<html lang="th">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width,initial-scale=1"
>

<title>เข้าสู่ระบบ - Library</title>

<link
    rel="stylesheet"
    href="assets/style.css"
>

</head>


<body class="login-page">


<div class="login-box">

    <h1>📚 Library System</h1>

    <p>ระบบจัดการคลังหนังสือ</p>


    <?php if ($error): ?>

        <div class="alert error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="post">

        <label>ชื่อผู้ใช้</label>

        <input
            type="text"
            name="username"
            autocomplete="username"
            required
        >


        <label>รหัสผ่าน</label>

        <input
            type="password"
            name="password"
            autocomplete="current-password"
            required
        >


        <button
            type="submit"
            class="btn primary full"
        >
            เข้าสู่ระบบ
        </button>

    </form>


    <small>
        ทดสอบ: admin / admin123
    </small>

</div>


</body>

</html>