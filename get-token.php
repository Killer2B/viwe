<?php
require 'config.php';
session_start();

$video_id = 'kirax-cnd-video';
$expires = time() + 60;
$user_fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
$token = hash_hmac('sha256', $video_id . $expires . $user_fingerprint, $secret_key);

echo json_encode([
  'url' => "stream.php?id=$video_id&expires=$expires&token=$token"
]);
?>