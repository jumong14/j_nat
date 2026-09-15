<?php

$SUPABASE_URL = "https://pkdcuasnwwglkdqqtiyl.supabase.co";
$SUPABASE_KEY = "sb_publishable_CxMFubG6LSWsDIz6wveLZQ_f9FiggJa";

function supabaseRequest($table, $method = "GET", $data = null, $query = "")
{
    global $SUPABASE_URL, $SUPABASE_KEY;

    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

    $url = $SUPABASE_URL . "/rest/v1/" . $table . $query;

    $headers = [
        "apikey: " . $SUPABASE_KEY,
        "Authorization: Bearer " . $SUPABASE_KEY,
        "Content-Type: application/json",
        "Accept: application/json",
        "Prefer: return=representation"
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,

        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,

        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    if (
        ($method === "POST" || $method === "PATCH")
        && $data !== null
    ) {
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);

        return [
            "code" => 500,
            "data" => [
                "error" => $error
            ]
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $decoded = json_decode($response, true);

    if (
        $decoded === null
        && $response !== "null"
        && $response !== ""
    ) {
        $decoded = [
            "raw_response" => $response
        ];
    }

    return [
        "code" => $httpCode,
        "data" => $decoded
    ];
}