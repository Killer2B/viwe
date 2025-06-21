<?php
require 'config.php';
session_start();

$id = $_GET['id'] ?? '';
$expires = $_GET['expires'] ?? '';
$token = $_GET['token'] ?? '';
$file = $_GET['file'] ?? 'master.m3u8';
$user_fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);

// التحقق من التوقيع
$expected = hash_hmac('sha256', $id . $expires . $user_fingerprint, $secret_key);
if (time() > $expires || !hash_equals($expected, $token)) {
    show_error("⛔ الرابط منتهي أو التوقيع غير صالح.");
}

// المصدر الأصلي للفيديو
$base_url = "https://player.odycdn.com/v6/streams/1bdecaf5f87b0d8800ceb87fc371810f3b8c3cc1/c9ca6edb54e60f26f5084bfc6f5963663e1eaf8b959a4901d0ac41f09d93d2ab88315a21d13b253363c44055674098e1/";
$remote_url = $base_url . $file;

// تحميل الملف من المصدر
$ch = curl_init($remote_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    show_error("⚠️ تعذر تحميل الملف من المصدر.");
}

// في حالة master.m3u8 عدّل روابط الجودات
if ($file === 'master.m3u8') {
    $response = preg_replace_callback('/^(v\d+\.m3u8)$/m', function ($matches) use ($id, $expires, $secret_key, $user_fingerprint) {
        $new_token = hash_hmac('sha256', $id . $expires . $user_fingerprint, $secret_key);
        return "stream.php?id=$id&expires=$expires&token=$new_token&file=" . $matches[1];
    }, $response);
}

// نوع الملف (MIME)
$ext = pathinfo($file, PATHINFO_EXTENSION);
switch ($ext) {
    case 'm3u8':
        header('Content-Type: application/vnd.apple.mpegurl');
        break;
    case 'ts':
        header('Content-Type: video/mp2t');
        break;
    default:
        header('Content-Type: application/octet-stream');
}

header('Access-Control-Allow-Origin: *');
header('Content-Disposition: inline');
echo $response;
exit;

// رسالة خطأ أنيقة
function show_error($msg) {
    http_response_code(403);
    echo "<!DOCTYPE html><html lang='ar'><head><meta charset='UTF-8'><title>رفض</title>
    <style>body{background:#111;color:#fff;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}
    .box{background:#222;padding:30px;border-radius:12px;box-shadow:0 0 20px #000;text-align:center}
    h1{color:#e74c3c}</style></head><body>
    <div class='box'><h1>🚫 تم حظر الوصول</h1><p>$msg</p></div>
    </body></html>";
    exit;
}
