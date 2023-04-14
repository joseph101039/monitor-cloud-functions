import base64
import os
import telegram

# from google.cloud import pubsub_v1

# 接收 http 訊息
# code reference: https://cloud.google.com/functions/docs/tutorials/http-1st-gen

def sendMessage(request):
     """ Responds to an HTTP request using data from the request body parsed
    according to the "content-type" header.
    Args:
        request (flask.Request): The request object.
        <https://flask.palletsprojects.com/en/1.1.x/api/#incoming-request-data>
    Returns:
        The response text, or any set of values that can be turned into a
        Response object using `make_response`
        <https://flask.palletsprojects.com/en/1.1.x/api/#flask.make_response>.
    """
     
     try: 
          content_type = request.headers['content-type']
          if content_type != 'application/json':
               raise ValueError("Not a json type")
          
          request_json = request.get_json(silent=True)
          

          if request_json and 'message' in request_json:
               message = request_json['message']
          else:
               raise ValueError("JSON is invalid, or missing a 'name' property")



          print(request_json) # todo remove


          data = base64.b64decode(message['data']).decode('utf-8')


          
          # 設置 bot 的 token
          bot_token = os.environ.get('BOT_TOKEN')
          chat_id = os.environ.get('CHAT_ID')

          bot = telegram.Bot(token=bot_token)
          maxlen = 1000

          # 傳送訊息
          message = data[:maxlen]
          text = f"[Python] 接收到 log 訊息 : {message}"
          bot.send_message(chat_id=chat_id, text=text)

          return 'ok'
     except Exception as e:
          print(e)
          raise e
          
     return None
          

