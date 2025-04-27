<?php

/**
 * Translates text using the Google Translate API without caching.
 *
 * @param string $text The text to translate.
 * @param string $targetLanguage The target language code (e.g., 'en', 'es', 'fr').
 * @return string The translated text.
 */
function translateTextGoogle_nocache($text, $targetLanguage) {
    // Replace with your actual Google Translate API Key
    $apiKey = "AIzaSyBOti4mM-6x9WDnZIjIeyEU21OpBXqWBgw";

    /**
     * The source language is set to 'auto' for automatic detection.
     * @var string $sourceLanguage
     */
    $sourceLanguage = "auto";

    $text = urlencode($text);

    /**
     * Make a request to the Google Translate API.
     * @var string $response
     */
    $response = file_get_contents("https://translation.googleapis.com/language/translate/v2?key=$apiKey&target=$targetLanguage&q=$text");

    $response = json_decode($response, true);

    /**
     * Extract the translated text from the API response.
     * @var string $res
     */
    $res = $response['data']['translations'][0]['translatedText'];
    return $res;
}

/**
 * Translates text using the Google Translate API with caching.
 *
 * This function first checks if the translation is already cached. If it is,
 * the cached result is returned. Otherwise, it calls the Google Translate API,
 * caches the result, and then returns it.
 *
 * @param string $text The text to translate.
 * @param string $targetLanguage The target language code (e.g., 'en', 'es', 'fr').
 * @return string The translated text.
 */
function translateTextGoogle($text, $targetLanguage) {
    /**
     * Generate a unique cache key based on the text and target language.
     * @var string $cache_key1
     */
    $cache_key1 = "TEXT:".$text .";LANGUAGE:". $targetLanguage;
    $cache_key = md5($cache_key1);
    
    /** @var string $cache_dir The directory where the cache files are stored. */
    $cache_dir = '/var/cache/portal/google/';
    $cache_file = $cache_dir . $cache_key . '.txt';

    // Проверяем, существует ли закешированный файл
    if (file_exists($cache_file)) {
        // Если файл существует, возвращаем его содержимое
        $cached=file_get_contents($cache_file);
        echo ("from cache:");
        echo ($cached);
      return $cached;
    } else {        
        $result = translateTextGoogle_nocache($text, $targetLanguage);

        file_put_contents($cache_file, $result);

        return $result;
    }
}

//echo(translateTextGoogle("Hello","zh_CN"));
