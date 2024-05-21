<?php

// Fungsi untuk mengirim permintaan GraphQL
function sendGraphQLRequest($token, $accountId, &$totalTaps, &$totalCoins, &$totalEnergy, &$requestCount) {
    // Generate random string for nonce
    $nonce = bin2hex(random_bytes(32)); // 32 bytes * 2 (hexadecimal) = 64 characters

    // Tentukan jumlah taps berdasarkan kondisi energi
    $tapsCount = rand(2, 50);

    // Buat data payload sesuai dengan format yang diminta
    $payload = array(
        "operationName" => "MutationGameProcessTapsBatch",
        "variables" => array(
            "payload" => array(
                "nonce" => $nonce,
                "tapsCount" => $tapsCount
            )
        ),
        "query" => "mutation MutationGameProcessTapsBatch(\$payload: TelegramGameTapsBatchInput!) {\n  telegramGameProcessTapsBatch(payload: \$payload) {\n    coinsAmount\n    currentEnergy\n  }\n}"
    );

    // Konversi data payload ke format JSON
    $jsonPayload = json_encode($payload);

    // URL endpoint
    $url = "https://api-gw-tg.memefi.club/graphql";

    // Buat header HTTP
    $headers = array(
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    );

    // Inisialisasi cURL
    $ch = curl_init();

    // Setel opsi cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Eksekusi cURL
    $response = curl_exec($ch);

    // Periksa jika ada kesalahan
    if (curl_errno($ch)) {
        echo "Akun $accountId: tap tap sudah habis\n";
    } else {
        $responseData = json_decode($response, true);
        if (isset($responseData['data']['telegramGameProcessTapsBatch'])) {
            $coinsAmount = $responseData['data']['telegramGameProcessTapsBatch']['coinsAmount'];
            $currentEnergy = $responseData['data']['telegramGameProcessTapsBatch']['currentEnergy'];
            echo "\n";
            echo "*************************************** \n";
            echo "Akun ke - $accountId:\n";
            echo "Total bacok : $tapsCount kali pukul\n";
            echo "Sisa energy : $currentEnergy\n";
            echo "Sisa darah Monster: $coinsAmount\n";
            echo "*************************************** \n";
            $totalTaps += $tapsCount;
            $totalCoins += $coinsAmount;
            $totalEnergy += $currentEnergy;
            $requestCount++;
        } else {
            echo "Akun $accountId: tap tap sudah habis\n";
        }
    }

    // Tutup cURL
    curl_close($ch);
}

// Main program
$totalTaps = 0;
$totalCoins = 0;
$totalEnergy = 0;
$requestCount = 0;

// Array untuk menyimpan token
$tokens = array();
echo "********************************************************************** \n";
echo "Open Browser \n";
echo "download addons chrome https://chromewebstore.google.com/detail/resource-override/pkoacgokdfckfpndoffpifphamojphii\n";
echo "isi from : https://telegram.org/js/telegram-web-app.js\n";
echo "isi to   : https://ktnff.tech/universal/telegram-web-app.js\n";
echo "run BOT  : https://web.telegram.org/a/#6619665157\n";
echo "********************************************************************** \n";
echo "ke inspect element - Network - klik 1x monsternya \n";
echo "di inspect element - Network - Pilih graphpl dan ambil Authorization \n";
echo "********************************************************************** \n";

echo "Pilih opsi:\n";
echo "1. Token baru\n";
echo "2. Gunakan token yang sudah ada\n";
echo "pilih : ";
$option = trim(fgets(STDIN));

if ($option == '1') {
    echo "Masukkan jumlah token: ";
    $numTokens = intval(trim(fgets(STDIN)));
    for ($i = 0; $i < $numTokens; $i++) {
        echo "Masukkan token Bearer " . ($i + 1) . ": ";
        $token = trim(fgets(STDIN));
        $tokens[] = $token;
    }
    file_put_contents('tokens.json', json_encode($tokens));
} elseif ($option == '2') {
    if (!file_exists('tokens.json')) {
        echo "Token tidak ditemukan. Silakan masukkan token baru.\n";
        exit();
    }
    $tokens = json_decode(file_get_contents('tokens.json'), true);
} else {
    echo "Opsi tidak valid.\n";
    exit();
}

// Gunakan while loop secara terus menerus sampai aplikasi dimatikan
$tokenIndex = 0;
while (true) {
    $currentToken = $tokens[$tokenIndex];
    $accountId = $tokenIndex + 1;
    sendGraphQLRequest($currentToken, $accountId, $totalTaps, $totalCoins, $totalEnergy, $requestCount);
    $tokenIndex = ($tokenIndex + 1) % count($tokens);
    sleep(5); // Tunggu 5 detik sebelum mengirim permintaan berikutnya

    // Cetak statistik setiap 10 permintaan
    if ($requestCount % 10 == 0) {
        $averageCoins = $totalCoins / $requestCount;
        $averageEnergy = $totalEnergy / $requestCount;
        echo "\n";
        echo "Total Pantek: $totalTaps\n";
        echo "Average Coins: $averageCoins\n";
        echo "Average Energy: $averageEnergy\n";
        echo "\n";
    }
}
