<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class TelegramService
{
    private string $token;
    private string $apiUrl;

    public function __construct()
    {
        $this->token = trim(
            (string) app_config('telegram.bot_token', '')
        );

        if ($this->token === '') {
            throw new RuntimeException(
                'Telegram bot token is not configured.'
            );
        }

        $this->apiUrl =
            'https://api.telegram.org/bot'
            . $this->token
            . '/';
    }

    /**
     * Send a text message.
     */
    public function sendMessage(
        int|string $chatId,
        string $text,
        ?array $replyMarkup = null
    ): array {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        if ($replyMarkup !== null) {
            $data['reply_markup'] =
                json_encode(
                    $replyMarkup,
                    JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                );
        }

        return $this->request(
            'sendMessage',
            $data
        );
    }

    /**
     * Send a photo with optional caption.
     */
    public function sendPhoto(
        int|string $chatId,
        string $photo,
        ?string $caption = null
    ): array {
        $data = [
            'chat_id' => $chatId,
            'photo' => $photo,
        ];

        if ($caption !== null) {
            $data['caption'] = $caption;
        }

        return $this->request(
            'sendPhoto',
            $data
        );
    }

    /**
     * Answer an inline keyboard callback.
     */
    public function answerCallbackQuery(
        string $callbackQueryId,
        ?string $text = null
    ): array {
        $data = [
            'callback_query_id' => $callbackQueryId,
        ];

        if ($text !== null && trim($text) !== '') {
            $data['text'] = $text;
        }

        return $this->request(
            'answerCallbackQuery',
            $data
        );
    }

    /**
     * Get bot information.
     */
    public function getMe(): array
    {
        return $this->request('getMe');
    }

    /**
     * Set webhook.
     */
    public function setWebhook(
        string $url
    ): array {
        return $this->request(
            'setWebhook',
            [
                'url' => $url,
            ]
        );
    }

    /**
     * Remove webhook.
     */
    public function deleteWebhook(): array
    {
        return $this->request(
            'deleteWebhook'
        );
    }

    /**
     * Get webhook information.
     */
    public function getWebhookInfo(): array
    {
        return $this->request(
            'getWebhookInfo'
        );
    }

    /**
     * Get incoming updates using long polling.
     */
    public function getUpdates(
        int $offset = 0,
        int $timeout = 10
    ): array {
        $data = [
            'timeout' => $timeout,
        ];

        if ($offset > 0) {
            $data['offset'] = $offset;
        }

        return $this->request(
            'getUpdates',
            $data
        );
    }

    /**
     * Call Telegram Bot API.
     */
    private function request(
        string $method,
        array $data = []
    ): array {
        $url =
            $this->apiUrl
            . $method;

        $ch = curl_init($url);

        if ($ch === false) {
            throw new RuntimeException(
                'Unable to initialize cURL.'
            );
        }

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,

                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,

                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]
        );

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            curl_close($ch);

            throw new RuntimeException(
                'Telegram API request failed: '
                    . $error
            );
        }

        $httpCode =
            (int) curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        curl_close($ch);

        $decoded = json_decode(
            $response,
            true
        );

        if (
            !is_array($decoded)
            || !isset($decoded['ok'])
        ) {
            throw new RuntimeException(
                'Invalid response from Telegram API.'
            );
        }

        if (
            $httpCode < 200
            || $httpCode >= 300
            || $decoded['ok'] !== true
        ) {
            $description =
                (string) (
                    $decoded['description']
                    ?? 'Unknown Telegram API error.'
                );

            throw new RuntimeException(
                'Telegram API error: '
                    . $description
            );
        }

        return $decoded;
    }
}