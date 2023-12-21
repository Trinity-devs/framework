<?php

namespace trinity\services;

use trinity\exception\httpException\NotFoundHttpException;

class UrlParsingService
{

    public static function parseQuery(string $url): array
    {
        preg_match_all("/\{([^{}]+)\}/", $url, $matches);

        return $matches[1];
    }

    /**
     * @param string $url
     * @return array
     */
    public static function parseParams(string $url): array
    {
        if (str_contains($url, '?') === true) {
            return [];
        }

        $factoredUrl = explode('/', $url);
        $params = [];
        foreach ($factoredUrl as $value) {
            if (str_contains($value, '{') === true) {
                $param = array_search($value, $factoredUrl) - 1;
                $params[] = $factoredUrl[$param];
            }
        }

        return $params;
    }

    /**
     * @param string $url
     * @param array $params
     * @return string|false
     * @throws NotFoundHttpException
     */
    public static function parsePath(string $url, array $params): string|false
    {
        if (str_contains($url, '?')) {
            return parse_url($url, PHP_URL_PATH);
        }

        if (empty($params)) {
            throw new NotFoundHttpException('Страница не найдена');
        }

        return rtrim(strstr($url, $params[0], true), '/');
    }
}