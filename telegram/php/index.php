<?php

// require 'vendor/autoload.php';

// code reference: https://github.com/GoogleCloudPlatform/php-docs-samples/blob/main/functions/helloworld_http/index.php

use Google\CloudFunctions\FunctionsFramework;
use Psr\Http\Message\ServerRequestInterface;
use TelegramBot\Api\BotApi;
use Dotenv\Dotenv;



// Register the function with Functions Framework.
// This enables omitting the `FUNCTIONS_SIGNATURE_TYPE=http` environment
// variable when deploying. The `FUNCTION_TARGET` environment variable should
// match the first parameter.
FunctionsFramework::http('sendMessage', 'sendMessage');
const MAX_LEN = 1000;

function sendMessage(ServerRequestInterface $request): string
{

    if (file_exists(__DIR__ . "/.env")) {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    $botToken = $_ENV['BOT_TOKEN'];
    $chatId =  $_ENV['CHAT_ID'];    // 如果是 group 的 chat id, 在 group 達到 super group 條件時 chat id 會改變

    $text = getPubSubMessageData($request);
    $text = substr($text, 0, MAX_LEN);
    $text = sprintf("[PHP] 接收到 log 訊息 : %s", $text);
    $text = htmlspecialchars($text);

    $bot = new BotApi($botToken);
    $bot->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
    $bot->sendMessage($chatId, $text, 'HTML', false);

    return 'ok';
}


function getPubSubMessageData(ServerRequestInterface $request): string {

    $body = $request->getBody()->getContents();
    if (empty($body)) {
        throw new RuntimeException("missing http body");
    }

    $json = json_decode($body, true);
    if (json_last_error() != JSON_ERROR_NONE) {
        throw new RuntimeException(sprintf(
            'Could not parse body: %s',
            json_last_error_msg()
        ));
    }

    $payload = $json['message']['data'] ?? null;
    if ($payload === null) {
        throw new RuntimeException('Could not find column message.data');
    }


    $data = base64_decode($payload);
    if ($data === false) {
        throw new RuntimeException(sprintf(
            'Could not base64 decode message.data: %s',
            $payload
        ));
    }

    return $data;
}
