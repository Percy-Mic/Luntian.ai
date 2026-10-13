<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

use Luntian\Database;
use Luntian\Response;

$user = require_auth_user();
$pdo = Database::connection();
$userId = (int) $user['id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $sessionId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

    if ($sessionId !== null && $sessionId !== false) {
        $session = $pdo->prepare(
            'SELECT id, title, created_at, updated_at FROM chat_sessions WHERE id = :id AND user_id = :uid'
        );
        $session->execute(['id' => $sessionId, 'uid' => $userId]);
        $chat = $session->fetch();
        if (!$chat) {
            Response::error('Chat not found', 404);
        }

        $messages = $pdo->prepare(
            'SELECT id, role, content, created_at FROM chat_messages WHERE session_id = :id ORDER BY id ASC'
        );
        $messages->execute(['id' => $sessionId]);
        Response::json(['ok' => true, 'chat' => $chat, 'messages' => $messages->fetchAll()]);
    }

    $stmt = $pdo->prepare(
        'SELECT s.id, s.title, s.created_at, s.updated_at,
                (SELECT m.content FROM chat_messages m WHERE m.session_id = s.id ORDER BY m.id DESC LIMIT 1) AS last_message
         FROM chat_sessions s
         WHERE s.user_id = :uid
         ORDER BY s.updated_at DESC, s.id DESC
         LIMIT 100'
    );
    $stmt->execute(['uid' => $userId]);
    Response::json(['ok' => true, 'chats' => $stmt->fetchAll()]);
}

if ($method === 'DELETE') {
    $body = read_json_body();
    $sessionId = filter_var($body['sessionId'] ?? null, FILTER_VALIDATE_INT);
    if (!$sessionId || $sessionId < 1) {
        Response::error('A valid sessionId is required', 400);
    }

    $delete = $pdo->prepare('DELETE FROM chat_sessions WHERE id = :id AND user_id = :uid');
    $delete->execute(['id' => $sessionId, 'uid' => $userId]);
    if ($delete->rowCount() === 0) {
        Response::error('Chat not found', 404);
    }
    Response::json(['ok' => true, 'deletedSessionId' => (int) $sessionId]);
}

header('Allow: GET, DELETE, OPTIONS');
Response::error('Method not allowed', 405);
