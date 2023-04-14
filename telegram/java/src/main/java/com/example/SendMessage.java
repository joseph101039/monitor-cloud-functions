package com.example;

// required by cloud function
import com.google.cloud.functions.HttpFunction;
import com.google.cloud.functions.HttpRequest;
import com.google.cloud.functions.HttpResponse;
import java.io.BufferedWriter;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.io.BufferedInputStream;
import java.io.InputStream;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLDecoder;
import java.util.Optional;

import org.apache.commons.io.IOUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.commons.text.StringEscapeUtils;
import java.net.URLEncoder;
import java.util.Base64;

import org.json.JSONObject;

// code reference 1: https://github.com/GoogleCloudPlatform/java-docs-samples/blob/main/functions/http/http-method/src/main/java/functions/HttpMethod.java
// code reference 2: https://github.com/GoogleCloudPlatform/java-docs-samples/blob/main/functions/http/send-http-request/src/main/java/functions/SendHttpRequest.java
public class SendMessage implements HttpFunction{

    static final int MAX_LEN = 1000;

    @Override
    public void service(HttpRequest request, HttpResponse response) throws IOException {
        
        BufferedWriter writer = response.getWriter();
        try {
            
            Optional<String> contentType = request.getContentType();
            
            
            if (!contentType.equals(Optional.of("application/json"))) {
                throw new RuntimeException("content type is not application/json : " + contentType);
            }

            // read the request body
            Optional<String> charset = request.getCharacterEncoding();
            InputStream is = request.getInputStream();
            String payload = IOUtils.toString(is, charset.isEmpty() ? "UTF-8": charset.get())

            String logEntry = getMessageData(payload);

            sendToTelegram(logEntry);

            response.setStatusCode(HttpURLConnection.HTTP_OK);
            writer.write("ok");
            return;

        } catch (Exception e) {

            e.printStackTrace();
            response.setStatusCode(HttpURLConnection.HTTP_BAD_REQUEST);
            writer.write(e.getMessage());
        }
    }  

    private static void sendToTelegram(String logEntry) throws IOException    {

        
        String botToken = System.getenv("BOT_TOKEN");
        String chatId = System.getenv("CHAT_ID");


        // truncate the message length
        logEntry.substring(0, Math.min(MAX_LEN, logEntry.length()));

        String text = "[Java] 接收到 log 訊息 : ".concat(logEntry);
        text = StringEscapeUtils.escapeHtml4(text);
        text = URLEncoder.encode(text, "UTF-8");
        
        String urlString = "https://api.telegram.org/bot%s/sendMessage?parse_mode=HTML&chat_id=%s&text=%s";
        urlString = String.format(urlString, botToken, chatId, text);    
        
        
        URL url = new URL(urlString);
        URLConnection conn = url.openConnection();
        InputStream is = new BufferedInputStream(conn.getInputStream());
    }

    /**
     * 
     * @param str e.g. {"message":{"data":"dmVyYXRlc3Q=","messageId":"7430248718308510","message_id":"7430248718308510","publishTime":"2023-04-07T08:23:43.74Z","publish_time":"2023-04-07T08:23:43.74Z"},"subscription":"projects/rdm-common/subscriptions/host-error-test-java"}
     * @return
     */
    private static String getMessageData(String str) {
        
        JSONObject obj = new JSONObject(str); 
        String data = obj.getJSONObject("message").getString("data");      
        
        // base64 decode
        return new String(Base64.getDecoder().decode(data));
    }
}