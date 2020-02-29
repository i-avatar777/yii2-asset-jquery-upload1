# yii2-asset-jquery-upload1



Ресурс и контроллер для Yii2 который позволяет создать загрузчик файла на сервер

Содержит контроллер и действие для обработки прогресса загрузки и сохранения файла в файловой системе.

uploadDirectory in src/UploadControllerFileUpload8.php

сайт с которого принимать запросы

https://www.draw.io/#G1VN37M-uP9PQ34gl28gsd-di1D7nK2Tlf

![](image/model.png)

## Настройка

Файл `config.php`

```php
return [
    // ...
    'controllerMap' => [
        // ...
        'upload2' => '\iAvatar777\assets\JqueryUpload1\Upload2Controller',
        // ...
    ],
];
```

Файл `params.php`


## Пример использования

```php
\iAvatar777\assets\JqueryUpload1\JqueryUpload::register($this);
```

```js
var uploader = new ss.SimpleUpload({
      button: 'upload-btn', // HTML element used as upload button
      url: '/upload2/file-upload7', // URL of server-side upload handler
      name: 'uploadfile', // Parameter name of the uploaded file
      sessionProgressUrl: '/upload2/session-progress'
});
```

## Будущие разработки

Планируется сделать чтобы в контроллере была возможность еще сразу подрезать картинку под размер или наложить маску.