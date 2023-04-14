require 'telegram/bot'
require 'functions_framework'
require 'base64'
require 'strings'
require 'cgi'

# code reference: https://github.com/GoogleCloudPlatform/ruby-docs-samples/tree/main/functions/http/content

# This function receives an HTTP request of type Rack::Request
# and interprets the body as JSON. It prints the contents of
# the "message" field, or "Hello World!" if there isn't one.

FunctionsFramework.http "send_message" do |request|

  content_type = request.content_type
  if content_type != "application/json" 
      raise RuntimeError, "content type is not json" + content_type
  end
  
  # 取得內容
  input = JSON.parse request.body.read rescue {}
  data = input["message"]["data"].to_s
  data = Base64.decode64(data)

  # 準備發送訊息
  maxlen = 1000
  data = Strings.truncate(data, maxlen)
  text = "[Ruby] 接收到 log 訊息 : " + data
  CGI.escapeHTML(text)

  bot_token = ENV['BOT_TOKEN']
  chat_id = ENV['CHAT_ID']

  Telegram::Bot::Client.run(bot_token) do |bot|
      bot.api.send_message(chat_id: chat_id, text: text, parse_mode: "HTML")
  end

  "ok"
end
  




