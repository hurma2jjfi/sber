<?php

return [
    'paths' => ['api/*'], // Пути, для которых применяются правила CORS
    'allowed_methods' => ['*'], // Разрешенные HTTP-методы (GET, POST, PUT, DELETE и т.д.)
    'allowed_origins' => ['*'], // Разрешенные домены (здесь разрешены все домены)
    'allowed_origins_patterns' => [], // Регулярные выражения для доменов
    'allowed_headers' => ['*'], // Разрешенные заголовки
    'exposed_headers' => [], // Заголовки, которые будут доступны клиенту
    'max_age' => 0, // Время кэширования CORS-запросов (в секундах)
    'supports_credentials' => false, // Поддержка учетных данных (например, cookies)
];
