<?php
require 'vendor/autoload.php';

use TelegramBot\Api\BotApi;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$botToken = $_ENV['BOT_TOKEN'];
$chatId =  $_ENV['CHAT_ID'];    // 如果是 group 的 chat id, 在 group 達到 super group 條件時 chat id 會改變
$message = htmlspecialchars('Hello, World!');

$bot = new BotApi($botToken);
$bot->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
$bot->sendMessage($chatId, $message, 'HTML', false);
