<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use Luntian\Database;
use Luntian\GeminiClient;
use Luntian\Response;
use Luntian\UserRepository;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST, OPTIONS');
    Response::error('Method not allowed', 405);
}

$user = require_auth_user();
$body = read_json_body();
$message = trim((string) ($body['message'] ?? ''));
$sessionId = isset($body['sessionId']) ? (int) $body['sessionId'] : null;

if ($message === '') {
    Response::error('message is required', 400);
}
if (mb_strlen($message) > 8000) {
    Response::error('message too long (maximum 8,000 characters)', 400);
}

$pdo = Database::connection();
$userId = (int) $user['id'];
$createdSession = false;

if ($sessionId === null || $sessionId <= 0) {
    $title = mb_substr(preg_replace('/\s+/u', ' ', $message) ?? $message, 0, 60);
    $stmt = $pdo->prepare(
        'INSERT INTO chat_sessions (user_id, title) VALUES (:uid, :title) RETURNING id'
    );
    $stmt->execute(['uid' => $userId, 'title' => $title ?: 'New chat']);
    $sessionId = (int) $stmt->fetchColumn();
    $createdSession = true;
} else {
    $check = $pdo->prepare('SELECT id FROM chat_sessions WHERE id = :sid AND user_id = :uid');
    $check->execute(['sid' => $sessionId, 'uid' => $userId]);
    if (!$check->fetch()) {
        Response::error('Chat not found', 404);
    }
}

// Read context before inserting the current message. Limit to the latest 20 turns.
$historyStmt = $pdo->prepare(
    'SELECT role, content FROM (
        SELECT role, content, id
        FROM chat_messages
        WHERE session_id = :sid
        ORDER BY id DESC
        LIMIT 20
    ) recent ORDER BY id ASC'
);
$historyStmt->execute(['sid' => $sessionId]);
$history = $historyStmt->fetchAll();

$insertUser = $pdo->prepare(
    'INSERT INTO chat_messages (session_id, role, content) VALUES (:sid, :role, :content)'
);
$insertUser->execute(['sid' => $sessionId, 'role' => 'user', 'content' => $message]);

$systemPrompt = 'You are Luntian AI, a helpful, precise assistant for creative and technical work. '
    . 'Use the supplied conversation history when relevant. Do not claim to have performed actions you have not performed. '
    . 'Be concise unless the user asks for detail.';

try {
    $reply = GeminiClient::chat($message, $systemPrompt, $history);
} catch (\Throwable $e) {
    // Keep the user message for auditability, but don't expose provider or server details.
    error_log('Luntian chat generation failed: ' . $e->getMessage());
    Response::error('The AI response could not be generated. Your message was not lost; please retry.', 502);
}

$insertAi = $pdo->prepare(
    'INSERT INTO chat_messages (session_id, role, content) VALUES (:sid, :role, :content)'
);
$insertAi->execute(['sid' => $sessionId, 'role' => 'assistant', 'content' => $reply]);

$pdo->prepare('UPDATE chat_sessions SET updated_at = NOW() WHERE id = :id AND user_id = :uid')
    ->execute(['id' => $sessionId, 'uid' => $userId]);

Response::json([
    'ok' => true,
    'sessionId' => $sessionId,
    'reply' => $reply,
    'createdSession' => $createdSession,
    'isPro' => UserRepository::isPro($user),
]);
