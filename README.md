# bookmarks-rest

Сущности
--------
**Bookmark**:
```code
{
    "id": <int>,
    "url": <string>,
    "created_at": <datetime>,
    "comments": [
        <comment>,
        ...
    ]
}
```
**Comment**:
```code
{
    "id": <int>,
    "text": <string>,
    "ip": <string>,
    "created_at": <datetime>
}
```

**API**
---

**GET /bookmarks?limit=10** - получить список 10 последних добавленных Bookmark

Ответ:
```code
[
  {
    "id": "3",
    "url": "http://0.0.0.0:8080/bookmarks3",
    "created_at": "2016-11-06 14:43:49"
  },
  {
    "id": "2",
    "url": "http://0.0.0.0:8080/bookmarks2",
    "created_at": "2016-11-06 13:04:14"
  },
  {
    "id": "1",
    "url": "http://0.0.0.0:8080/bookmarks1",
    "created_at": "2016-11-06 13:04:09"
  }
]
```

**GET /bookmarks/url?url=http://0.0.0.0:8080/bookmarks2** - получить Bookmark с комментариями по Bookmark.url

Ответ:
```code
{
  "id": "2",
  "url": "http://0.0.0.0:8080/bookmarks2",
  "created_at": "2016-11-06 13:04:14",
  "comments": [
    {
      "id": "10",
      "text": "dfg88888888",
      "ip": "127.0.0.1",
      "created_at": "2016-11-06 14:40:48"
    },
    {
      "id": "4",
      "text": "asd",
      "ip": "127.0.0.1",
      "created_at": "2016-11-06 13:07:49"
    }
  ]
}
```
Если такого Bookmark ещё нет:
```code
{
  "error": "Bookmark not found"
}
```

**POST /bookmarks** - добавить Bookmark по url

Запрос:
```code
{
    "url": "http://0.0.0.0:8080/bookmarks3"
}
```
Ответ:
```code
{
  "id": "3",
  "url": "http://0.0.0.0:8080/bookmarks3",
  "created_at": "2016-11-06 14:43:49"
}
```

**POST /comments** - добавить Comment к Bookmark

Запрос:
```code
{
    "text": "88888888",
    "bookmark_id":3
}
```
Ответ:
```code
{
  "id": "12",
  "text": "88888888",
  "ip": "127.0.0.1",
  "created_at": "2016-11-06 16:04:18"
}
```

**PATCH /comments** - изменить Comment.text

Запрос:
```code
{
    "text": "88888888"
}
```
Ответ:
```code
```

При попытке изменить Comment.text с другим IP или больше чем через 1 час:
```code
{
  "error": "You do not have permissions"
}
```

**DELETE /comments/1** - удалить Comment

Ответ:
```code
```

При попытке удалить Comment с другим IP или больше чем через 1 час:
```code
{
  "error": "You do not have permissions"
}
```
