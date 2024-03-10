<?php

/* @var $exception \Throwable */
/* @var $handler trinity\http\ErrorHandlerhttp */

$code = $exception->getCode();
$name = $handler->getExceptionName($exception);
if ($name === null) {
    $name = 'Error';
}

if ($exception instanceof trinity\exception\httpException\HttpException) {
    $message = $exception->getMessage();
}
if ($exception instanceof trinity\exception\httpException\HttpException === false) {
    $message = 'Произошла внутренняя ошибка сервера.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= $handler->htmlEncode($name) ?></title>

    <style>
        body {
            font: normal 9pt "Verdana";
            color: #000;
            background: #fff;
        }

        h1 {
            font: normal 18pt "Verdana";
            color: #f00;
            margin-bottom: .5em;
        }

        h2 {
            font: normal 14pt "Verdana";
            color: #800000;
            margin-bottom: .5em;
        }

        h3 {
            font: bold 11pt "Verdana";
        }

        p {
            font: normal 9pt "Verdana";
            color: #000;
        }

        .version {
            color: rgb(128, 128, 128);
            font-size: 8pt;
            border-top: 1px solid #aaa;
            padding-top: 1em;
            margin-bottom: 1em;
        }
    </style>
</head>

<body>
    <h1><?= $handler->htmlEncode($name) ?></h1>
    <h2><?= nl2br($handler->htmlEncode($message)) ?></h2>
    <p>
        Вышеупомянутая ошибка произошла во время обработки вашего запроса веб-сервером.
    </p>
    <p>
        Пожалуйста, свяжитесь с нами, если вы считаете, что это ошибка сервера. Спасибо.
    </p>
    <div class="version">
        <?= date('Y-m-d H:i:s') ?>
    </div>
</body>
</html>
