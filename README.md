code implement:
reference doc: 




# 編寫 Cloud function resolve message


請查看範例文件:
https://cloud.google.com/functions/docs/writing/write-event-driven-functions?hl=zh-cn

對於第二代 cloud function, 傳入函式格式為 CloudEvent，請參照文件格式

# 設置 pubsub 串接 cloud function

請查看範例文件: 
https://cloud.google.com/functions/docs/tutorials/pubsub?hl=zh-cn



# PubSub message


經過 pubsub 傳遞訊息後, log-based metric 的原始訊息會放到 `data` 屬性中，
cloud function 必須將內容取出後解析，
以下為 [pubsub 所傳送訊息格式](https://cloud.google.com/pubsub/docs/reference/rest/v1/PubsubMessage)

```json
{
  "data": string,
  "attributes": {
    string: string,
    ...
  },
  "messageId": string,
  "publishTime": string,
  "orderingKey": string
}

```

其中各欄位

data：以 Base64 編碼的日誌資料。可以使用解碼該資料並讀取其中的內容。
attributes：附加的屬性資料，其中包含事件的詳細資訊。其中 attributes\["logging.googleapis.com/labels"\] 包含所有標記資訊。
messageId：消息的唯一 ID。
publishTime：消息發佈的 RFC3339 時間戳記。



data 內放置的 log 格式範例為，依據不同輸出資源有所不同

```json

{
  "insertId": "9qw8g5w0v8kmh0la",
  "labels": {
  },
  "logName": "projects/project-id/logs/stdout",
  "receiveTimestamp": "1999-01-01T01:37:35.736167158Z",
  "resource": {
  "labels": {
    "cluster_name": "cluster-name",
      "container_name": "container-name",
      "location": "asia-east1-a",
      "namespace_name": "namespace",
      "pod_name": "pod-name",
      "project_id": "project-id",
    },
    "type": "k8s_container",
  },
  "severity": "INFO",
  "textPayload": "raw text payload",
  "timestamp": "1999-01-01T01:37:32.796149558Z",
}
```