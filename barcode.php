<?php

require_once "config/database.php";
require_once "config/auth.php";

require_login();


$id = (int)($_GET["id"] ?? 0);


if ($id <= 0) {

    die("ไม่พบรหัสหนังสือ");

}


$result = supabaseRequest(
    "books",
    "GET",
    null,
    "?id=eq." . $id . "&select=*"
);


$books = $result["data"] ?? [];

$book = $books[0] ?? null;


if (!$book) {

    die("ไม่พบหนังสือ");

}


$barcode = trim(
    $book["barcode"] ?? ""
);


if ($barcode === "") {

    die("หนังสือเล่มนี้ยังไม่มี Barcode");

}


$title = $book["title"] ?? "";

$author = $book["author"] ?? "";

?>


<!DOCTYPE html>

<html lang="th">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Barcode - <?= htmlspecialchars($title) ?>
    </title>


    <script
        src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"
    ></script>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            padding: 30px;

            background: #f2f2f2;

            font-family:
                Arial,
                "Noto Sans Thai",
                sans-serif;

            text-align: center;

        }


        .label {

            width: 400px;

            min-height: 220px;

            margin: 30px auto;

            padding: 25px;

            background: white;

            border: 1px solid #ddd;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

        }


        .title {

            font-size: 20px;

            font-weight: bold;

            margin-bottom: 8px;

        }


        .author {

            font-size: 14px;

            margin-bottom: 15px;

        }


        .barcode {

            max-width: 100%;

            height: 90px;

        }


        .code {

            margin-top: 8px;

            font-size: 16px;

            font-weight: bold;

            letter-spacing: 2px;

        }


        .buttons {

            margin-top: 20px;

        }


        button {

            padding: 10px 20px;

            border: 0;

            border-radius: 6px;

            cursor: pointer;

            font-size: 16px;

            margin: 5px;

        }


        .print {

            background: #222;

            color: white;

        }


        .back {

            background: #ddd;

            color: #222;

        }


        @media print {

            body {

                padding: 0;

                background: white;

            }


            .label {

                margin: 0;

                border: none;

                width: 100%;

                min-height: auto;

            }


            .buttons {

                display: none;

            }

        }

    </style>

</head>


<body>


<div class="label">


    <div class="title">

        <?= htmlspecialchars($title) ?>

    </div>


    <?php if ($author !== ""): ?>

    <div class="author">

        <?= htmlspecialchars($author) ?>

    </div>

    <?php endif; ?>


    <svg
        id="barcode"
        class="barcode"
    ></svg>


    <div class="code">

        <?= htmlspecialchars($barcode) ?>

    </div>

</div>


<div class="buttons">


    <button
        class="print"
        onclick="window.print()"
    >
        🖨️ พิมพ์ Barcode
    </button>


    <button
        class="back"
        onclick="history.back()"
    >
        ← กลับ
    </button>

</div>


<script>

JsBarcode(
    "#barcode",
    <?= json_encode($barcode) ?>,
    {

        format: "CODE128",

        width: 2,

        height: 80,

        displayValue: false,

        margin: 10

    }
);

</script>


</body>

</html>

