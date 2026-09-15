<?php

require_once "config/auth.php";

require_login();

$target = $_GET["target"] ?? "borrow";

if ($target !== "return") {
    $target = "borrow";
}

include "partials/header.php";

?>

<h1>📷 สแกน Barcode</h1>

<p>
    อนุญาตให้เว็บไซต์ใช้กล้อง แล้วสแกน Barcode ของหนังสือ
</p>


<!-- ========================================
     เพิ่ม: สแกนจากรูปในเครื่อง
======================================== -->

<div class="scan-file-box">

    <h2>🖼️ หรือเลือก Barcode จากรูปในเครื่อง</h2>

    <p>
        เลือกรูป Barcode ที่บันทึกไว้ในคอมพิวเตอร์
    </p>

    <input
        type="file"
        id="barcodeImage"
        accept="image/*"
    >

</div>


<!-- ========================================
     ระบบกล้องเดิม
======================================== -->

<h2>📹 สแกนด้วยกล้อง</h2>

<div id="reader"></div>


<div
    id="result"
    class="scan-result"
>
    ยังไม่ได้สแกน
</div>


<script
    src="https://unpkg.com/html5-qrcode"
    type="text/javascript"
></script>


<script>

const target = <?= json_encode($target) ?>;

let scanned = false;


/* ========================================
   ฟังก์ชันส่ง Barcode
======================================== */

function goToBorrowOrReturn(decodedText) {

    if (scanned) {
        return;
    }

    scanned = true;


    document.getElementById("result").textContent =
        "พบ Barcode: " + decodedText;


    const barcode =
        encodeURIComponent(decodedText);


    if (target === "return") {

        window.location.href =
            "borrow.php?target=return&barcode=" + barcode;

    } else {

        window.location.href =
            "borrow.php?target=borrow&barcode=" + barcode;

    }

}


/* ========================================
   กล้องเดิม
======================================== */

function onScanSuccess(decodedText) {

    goToBorrowOrReturn(decodedText);

}


function onScanFailure(error) {

    // ไม่ต้องทำอะไรระหว่างที่ยังสแกนไม่สำเร็จ

}


const scanner = new Html5QrcodeScanner(

    "reader",

    {
        fps: 10,

        qrbox: {
            width: 250,
            height: 150
        }
    },

    false

);


scanner.render(
    onScanSuccess,
    onScanFailure
);


/* ========================================
   เพิ่ม: อ่าน Barcode จากรูป
======================================== */

document
    .getElementById("barcodeImage")
    .addEventListener("change", function(event) {

        const file = event.target.files[0];

        if (!file) {
            return;
        }


        document.getElementById("result").textContent =
            "🔍 กำลังอ่าน Barcode จากรูป...";


        const imageScanner =
            new Html5Qrcode("reader");


        imageScanner
            .scanFile(file, true)

            .then(decodedText => {

                goToBorrowOrReturn(decodedText);

            })

            .catch(error => {

                console.log(error);

                document.getElementById("result").textContent =
                    "❌ อ่าน Barcode จากรูปไม่สำเร็จ";

                scanned = false;

            });

    });

</script>


<style>

.scan-file-box {

    max-width: 600px;

    margin: 20px auto;

    padding: 20px;

    background: #f5f5f5;

    border-radius: 10px;

    text-align: center;

}


.scan-file-box h2 {

    margin-top: 0;

}


.scan-file-box input[type="file"] {

    margin-top: 10px;

    padding: 10px;

    width: 100%;

    background: white;

    border-radius: 6px;

}

</style>


<?php include "partials/footer.php"; ?>