<?php

declare(strict_types=1);

namespace Luntian;

final class GeminiClient
{
    /**
     * Generate a reply using recent persisted conversation turns.
     * History is intentionally bounded by the caller to control latency and token use.
     *
     * @param array<int, array{role:string,content:string}> $history
     */
    public static function chat(string $message, ?string $systemPrompt = null, array $history = []): string
    {
        $apiKey = env('GEMINI_API_KEY');
        if ($apiKey === null || $apiKey === '') {
            Response::error('Gemini API is not configured on the server.', 503);
        }

        $model = env('GEMINI_MODEL', 'gemini-3.5-flash-lite');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($model)
            . ':generateContent?key=' . rawurlencode($apiKey);

        $contents = [];
        foreach ($history as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'model' : 'user';
            $content = trim((string) ($turn['content'] ?? ''));
            if ($content === '') {
                continue;
            }
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $content]],
            ];
        }
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]],
        ];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
            ],
        ];
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemPrompt]]];
        }

        $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $encoded,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
        ]);

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || $raw === false) {
            Response::error('Could not reach Gemini API. Please try again.', 502);
        }

        $data = json_decode($raw, true);
        if ($status >= 400) {
            $providerMessage = $data['error']['message'] ?? 'Gemini request failed';
            error_log('Gemini API error (' . $status . '): ' . (string) $providerMessage);
            Response::error(
                $status === 429 ? 'AI usage is temporarily rate-limited. Please try again shortly.' : 'The AI provider could not complete this request.',
                $status === 429 ? 429 : 502
            );
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($text) || trim($text) === '') {
            Response::error('The AI returned an empty response. Please try again.', 502);
        }

        return trim($text);
    }
}
