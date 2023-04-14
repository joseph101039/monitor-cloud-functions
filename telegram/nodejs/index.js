'use strict';

const TelegramBot = require('node-telegram-bot-api');
const functions = require('@google-cloud/functions-framework');
const escapeHtml = require('escape-html');


// code reference: https://github.com/GoogleCloudPlatform/nodejs-docs-samples/blob/main/functions/http/httpContent/index.js#L16

/**
 * Responds to an HTTP request using data from the request body parsed according
 * to the "content-type" header.
 *
 * @param {Object} req Cloud Function request context.
 * @param {Object} res Cloud Function response context.
 */
functions.http('sendMessage', (req, res) => {
    if (req.get('content-type') !== 'application/json') {
        throw new Error(`request body is not a json type: ${req.get('content-type')}`);
    }

    let bot_token = process.env.BOT_TOKEN
    let chat_id = process.env.CHAT_ID

    // 限制長度與跳脫 html 字元
    const MAX_LEN = 1000;
    const data = req.body['message']['data'];
    const dataString = Buffer.from(data, 'base64')
        .toString('utf-8')
        .substring(0, MAX_LEN)

    let text =  escapeHtml(`[NodeJS] 接收到 log 訊息 : ${dataString}`);

     // 傳送訊息
    const bot = new TelegramBot(bot_token);

    bot.sendMessage(chat_id, text, {
        parse_mode: 'HTML',
        disable_web_page_preview: false
    })
    .then(() => {
        res.status(200).send('ok'); 
    })
    .catch((error) => {
        console.log(error.code);  // e.g. => 'ETELEGRAM'
        console.log(error.response.body); // e.g. => { ok: false, error_code: 400, description: 'Bad Request: chat not found' }
        res.status(400).send(error.response.body);
    });
});