// code reference 1 HelloHttp : https://github.com/GoogleCloudPlatform/dotnet-docs-samples/blob/main/functions/helloworld/HelloHttp/Function.cs
// code reference 2 HttpRequestMethod : https://github.com/GoogleCloudPlatform/dotnet-docs-samples/blob/main/functions/http/HttpRequestMethod/Function.cs

using Google.Cloud.Functions.Framework;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using System.IO;
using System.Text.Json;
using System.Threading.Tasks;
using System.Net.Http;
using System.Web;

namespace SimpleHttpFunction;

public class Function : IHttpFunction
{
    private readonly ILogger _logger;

    public Function(ILogger<Function> logger) =>
        _logger = logger;

    public async Task HandleAsync(HttpContext context)
    {
        HttpRequest request = context.Request;

         if (request.ContentType  != "application/json") {
            context.Response.StatusCode = 400;
            await context.Response.WriteAsync($"Content type must be application/json, but get {request.ContentType}");
            return;
        }
        
        // resolve the request body and fetch the content of the "message.data" field
        Task<string> logEntryTask = getMessageDataAsync(request);      
        

        // send telegram message
        var botToken = System.Environment.GetEnvironmentVariable("BOT_TOKEN");
        var chatId = System.Environment.GetEnvironmentVariable("CHAT_ID");

        string logEntry = await logEntryTask;
        var text = $"[.NET] 接收到 log 訊息 : {logEntry}";
        text = HttpUtility.UrlEncode(text, System.Text.Encoding.UTF8);
        var apiUrl = $"https://api.telegram.org/bot{botToken}/sendMessage?chat_id={chatId}&text={text}&parse_mode=HTML";

        using var client = new HttpClient();
        var response = await client.GetAsync(apiUrl);
        var responseBody = await response.Content.ReadAsStringAsync();
        
        context.Response.StatusCode = 200;
        await context.Response.WriteAsync("ok");
    }
    
    private async Task<string> getMessageDataAsync(HttpRequest request) 
    {
       
        // todo: implement
        // If there's a body, parse it as JSON and check for "name" field.
        using TextReader reader = new StreamReader(request.Body);
        string text = await reader.ReadToEndAsync();
        if (text.Length == 0) 
        {
            throw new System.Exception("missing request body");
        }


        string data = "";
        try
        {
            JsonElement json = JsonSerializer.Deserialize<JsonElement>(text);
            if (json.TryGetProperty("message", out JsonElement messageElement) &&
                messageElement.ValueKind == JsonValueKind.Object)
            {
                data = messageElement.GetProperty("data").GetString();
            }
        }
        catch (JsonException parseException)
        {
            _logger.LogError(parseException, "Error parsing JSON request");
        }


        // base64 decode
        byte[] bytes = System.Convert.FromBase64String(data);
        data = System.Text.Encoding.UTF8.GetString(bytes);


        // truncate the string if too long
        data = data.Substring(0, System.Math.Min(1000, data.Length));

        // escape html special character
        data = System.Net.WebUtility.HtmlEncode(data);
        return data;
    }
}