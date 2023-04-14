package monitor

import (
	"context"
	"fmt"
	"html"
	"os"
	"strconv"

	tgbotapi "github.com/go-telegram-bot-api/telegram-bot-api"
)

// code reference 1: https://cloud.google.com/functions/docs/samples/functions-helloworld-pubsub
// code reference 2: https://cloud.google.com/functions/docs/samples/functions-log-stackdriver#functions_log_stackdriver-go

// PubSubMessage is the payload of a Pub/Sub event.
// See the documentation for more details:
// https://cloud.google.com/pubsub/docs/reference/rest/v1/PubsubMessage
type PubSubMessage struct {
	Data []byte `json:"data"`	// Automatically decoded from base64.
}


// Function myHTTPFunction is an HTTP handler
func SendMessage(ctx context.Context, m PubSubMessage) error {

	botToken := os.Getenv("BOT_TOKEN")
	chatId := os.Getenv("CHAT_ID") // 如果是 group 的 chat id, 在 group 達到 super group 條件時 chat id 會改變

	if botToken == "" || chatId == "" {
		return fmt.Errorf("missing cloud funciton runtime environment variables")
	}


	bot, err := tgbotapi.NewBotAPI(botToken)
	if err != nil {
		return(err)
	}

	intChatId, err := strconv.ParseInt(chatId, 10, 64)
	if err != nil {
		return(err)
	}

	const maxlen = 1000
	data := truncateLog(string(m.Data), maxlen)
	data = html.EscapeString(data)
	text := fmt.Sprintf("[Go] 接收到 log 訊息 : %s ", data)
	

	message := tgbotapi.NewMessage(intChatId, text)
	message.ParseMode = tgbotapi.ModeHTML

	_, err = bot.Send(message)
	if err != nil {
		return err
	}

	return nil
}

// truncateLog 需要考慮 telegram 單一訊息限制最大長度
func truncateLog(data string, maxlen int) string {
	if len(data) > maxlen {
		return data[:maxlen]
	}

	return data
}
