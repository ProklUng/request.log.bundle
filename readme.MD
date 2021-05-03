# Бандл для создания и использования моков Response

Форк [пакета](https://github.com/mRoca/MrocaRequestLogBundle). Доработан под личные нужды, плюс добавил некоторые экспериментальные
битриксовые наработки (получилось не стабильно).

## Зачем?

Удобно генерировать фикстуры для функциональных тестов из ответов внешнего API. 

## Основное
 
1) Добавляешь заголовок `x-generate-response-mock` к запросу - получаешь мок в виде файла. Если мок уже существует,
то отдаются данные из него

***Известные ограничения***

Если GET запрос очень длинный - превышает 255 символов, - то облом, сохранить мок не удается.

## Битриксовые дела

Если бандл грузится не из под Битрикса, то соответствующие сервисы удаляются из контейнера.

Идея - создавать моки по url, использующим нативные битриксовые средства (или даже статические страницы).

В конфигурации бандла (файл `/local/config/packages/request_log.yaml`) в секции `bitrix_uri_list` указываются
regex паттерны страниц, подвергающихся обработке.

# Оригинальная документация

## Description

This bundle allows to log HTTP requests and associated responses as json files.
This generated json files can be used as API mock in order to test a front app without running the api.

## How it works ?

After each request (`Kernel::TERMINATE` event) containing the `x-generate-response-mock` header, a json file is created
containing the request and the response.

**Examples :**

> GET /categories

`app/log/mocks/categories/GET__.json`

```json
{
    "request": {
        "uri": "/categories",
        "method": "GET",
        "parameters": [],
        "content": ""
    },
    "response": {
        "statusCode": 200,
        "contentType": "application/json",
        "content": {
            "@context": "/contexts/Category",
            "@id": "/categories",
            "hydra:member": [
                {"name": "foo"},
                {"name": "bar"}
            ]
        }
    }
}
```

> PUT /categories/1 {"foo": "bar"}

`app/log/mocks/categories/PUT__1-a5e74.json`
```json
{
    "request": {
        "uri": "/categories/1",
        "method": "PUT",
        "parameters": [],
        "content": {
            "foo": "bar"
        }
    },
    "response": {
        "statusCode": 204,
        "contentType": "application/json",
        "content": ""
    }
}
```

**File naming strategy**

All files are created with the following convention :

`uri/METHOD__segments{--sorted-query=string&others}{__<sha1_substr5(sortedJsonContent)>}{__<sha1_substr5(sortedPostParameters)>}.json`

*Examples* :

URL                                                         | Filename
----------------------------------------------------------- | ----------------------------------------------------
GET /                                                       | GET__.json
GET /categories                                             | categories/GET__.json
GET /categories/1                                           | categories/GET__1.json
GET /categories?search[category][]=foo                      | categories/GET__--search%5Bcategory%5D%5B%5D=foo.json
GET /categories?order[foo]=asc&order[bar]=desc              | categories/GET__--order%5Bbar%5D=desc&order%5Bfoo%5D=asc.json
GET /categories?parent=/my/iri                              | categories/GET__--parent=%2Fmy%2Firi.json
POST /categories PARAMS: foo1=bar1; foo2=bar2               | categories/POST____3e038.json
POST /categories CONTENT: {"foo1":"bar1", "foo2":"bar2"}    | categories/POST____3e038.json
PUT /categories/1 CONTENT: {"foo2":"bar2", "foo1":"bar1"}   | categories/POST__1__3e038.json

    The filenames query strings can be hashed by setting the `hash_query_params` option to `true`.
    For example, `categories/GET__--order[bar]=desc&order[foo]=asc.json` will be `categories/GET__--b0324.json`

    The filenames query strings with non-asssocitive arrays are not indexed by default : `?foo[]=bar`.
    You can use the indexed format by setting the `use_indexed_associative_array` option to `true` : `?foo[0]=bar`.

```bash
composer require --dev proklung/request-log-bundle
```

*If necessary*, configure the bundle to your needs (example with default values):

```yaml
# app/config/config_dev.yml

request_log:
    mocks_dir: %kernel.logs_dir%/mocks/
    hash_query_params: false
    use_indexed_associative_array: false
    # Битриксовые URL (нестабильный функционал)
    bitrix_uri_list:
     # - '/^\/clubs\/$/'
     #  - '/^\/about\/$/'
     # - '/^\/xxx\/$/'
```

**If your are using the NelmioCorsBundle** or another CORS protection, you must add the header in the allowed ones :

```yaml
nelmio_cors:
    defaults:
        allow_headers: ['x-generate-response-mock']
```

## Usage

The request & response logger is not always activated. To log a request, add the `x-generate-response-mock` header into your request :

```
GET /categories HTTP/1.1
Host: api.my.domain
x-generate-response-mock: true
```

## Commands

Some useful commands are available to manager your mocks :

### Clear all mocks

```bash
app/console mroca:request-log:clear 
```

### Save mocks in a target directory

```bash
app/console mroca:request-log:dump /tmp/mocksdirtarget
```
