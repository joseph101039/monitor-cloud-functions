<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

/**
 * PHP google/apiclient 套件目前不包含此 API
 * Google Chat - Space API
 * 
 * https://developers.google.com/chat/api/reference/rest/v1/spaces.messages?hl=zh-tw#Message
 */ 

//$space_id = $_ENV['SPACE_ID'];
//$message = 'Hello World!';
//
//
//$json = json_encode(['text' => $message], JSON_UNESCAPED_SLASHES); // basic text, but you can use cards as well
//            $ch = curl_init("https://chat.googleapis.com/v1/spaces/$space_id"); // change to space ID, e.g. https://chat.googleapis.com/v1/spaces/...
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            $result = curl_exec($ch);
//            curl_close($ch);
//
//return;



/**
 * 1. 建立 Google Cloud Platform 帳戶，建立項目，並啟用 Google Chat API。
 * 2. To use Chat API, first enable Chat API by visiting https://console.cloud.google.com/marketplace/product/google/chat.googleapis.com and configure a Chat app by visiting https://console.cloud.google.com/apis/api/chat.googleapis.com/hangouts-chat. To learn more, visit https://developers.google.com/chat/quickstart/gcf-app.
 * 3. 在 GCP Console 中，建立服務帳戶，並授予其使用 Google Chat API 的權限。將服務帳戶的認證資訊下載至本機端。
 * 4. 在 PHP 專案中，使用 Google APIs Client Library for PHP，建立 Google_Client 實例，並使用服務帳戶的認證資訊進行身分驗證。
 * 5. 使用 Message 項目建立訊息，並將其包裝在 Cards 項目中，以符合 Google Chat API 要求的格式。
*/
# https://github.com/googleapis/google-api-php-client/issues/2192

// 建立 Google_Client 實例，並使用服務帳戶的認證資訊進行身分驗證
$client = new Google\Client();
$client->setApplicationName('My Chatbot');
$client->setScopes([
    'https://www.googleapis.com/auth/chat.spaces.create',
    'https://www.googleapis.com/auth/chat.bot'
]);

$client->setAuthConfig($_ENV['GOOGLE_APPLICATION_CREDENTIALS']); // 將路徑替換成服務帳戶的認證資訊的路徑

 
// 1. spaces.messages.create API: https://developers.google.com/chat/api/reference/rest/v1/spaces.messages/create
// 2. About spaces & group conversations: https://support.google.com/chat/answer/7659784?hl=en

$parent = "spaces/{$_ENV['SPACE_NAME']}";
$text = 'Hello World !';
$service = new Google\Service\HangoutsChat($client);

$response = $service->spaces->listSpaces();

print_r($response);
return;

// 建立訊息
$message = new Google\Service\HangoutsChat\Message();

// 設定傳輸訊息
$message->setText($text);
$message = $service->spaces_messages->create($parent, $message);

echo 'Message sent with message ID: ' . $message->getName();
return;


# todo another test 1: // Send Message with webhook:
# todo another test 2:
# todo 加入 preview 計畫: https://developers.google.com/workspace/preview
// ref: https://stackoverflow.com/questions/72773202/google-chat-api-can-t-create-space-method-not-found

# composer require google/auth
# composer require guzzlehttp/guzzle

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;

$scopes = [
        'https://www.googleapis.com/auth/chat.spaces.create',
        'https://www.googleapis.com/auth/chat.bot'
    ];

// create middleware
$middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
$stack = HandlerStack::create();
$stack->push($middleware);



// create the HTTP client
$client = new Client([
    'headers' => ['Content-Type' => 'application/json'],
    'handler' => $stack,
    'base_uri' => 'https://www.googleapis.com',
    'auth' => 'google_auth'  // authorize all requests
]);


// make the request
$response = $client->post( 'https://chat.googleapis.com/v1/spaces', [
    RequestOptions::JSON => [
        'name' => 'ABCDEFG',
        'spaceType' => 'DIRECT_MESSAGE',
        'threaded' => false,
        'displayName' => 'TestSpace'
    ],
]);