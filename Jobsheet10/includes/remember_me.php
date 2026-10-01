<?php
function rememberCookieOptions(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function clearRememberCookie(): void
{
    setcookie('remember_me', '', rememberCookieOptions(time() - 3600));
    unset($_COOKIE['remember_me']);
}

function issueRememberToken(PDO $pdo, int|string $userId, ?int $expiresAt = null): void
{
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $expiresAt ??= time() + 60 * 60 * 24 * 30;

    $stmt = $pdo->prepare(
        'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
         VALUES (:user_id, :selector, :token_hash, to_timestamp(:expires_at))'
    );
    $stmt->execute([
        'user_id' => $userId,
        'selector' => $selector,
        'token_hash' => hash('sha256', $validator),
        'expires_at' => $expiresAt,
    ]);

    setcookie('remember_me', $selector . '.' . $validator, rememberCookieOptions($expiresAt));
}

function restoreRememberedUser(PDO $pdo): bool
{
    $cookie = $_COOKIE['remember_me'] ?? '';
    $parts = explode('.', $cookie, 2);
    if (count($parts) !== 2 || !preg_match('/^[a-f0-9]{24}$/', $parts[0]) || !preg_match('/^[a-f0-9]{64}$/', $parts[1])) {
        clearRememberCookie();
        return false;
    }

    [$selector, $validator] = $parts;
    $stmt = $pdo->prepare(
        'SELECT t.user_id, t.token_hash, u.nama, u.role
         FROM remember_tokens t
         JOIN users u ON u.id = t.user_id
         WHERE t.selector = :selector AND t.expires_at > CURRENT_TIMESTAMP'
    );
    $stmt->execute(['selector' => $selector]);
    $remembered = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$remembered || !hash_equals($remembered['token_hash'], hash('sha256', $validator))) {
        $delete = $pdo->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
        $delete->execute(['selector' => $selector]);
        clearRememberCookie();
        return false;
    }

    $delete = $pdo->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
    $delete->execute(['selector' => $selector]);

    session_regenerate_id(true);
    $_SESSION['user_id'] = $remembered['user_id'];
    $_SESSION['nama'] = $remembered['nama'];
    $_SESSION['role'] = $remembered['role'];
    issueRememberToken($pdo, $remembered['user_id']);
    return true;
}

function revokeRememberToken(PDO $pdo, string $cookie): void
{
    $selector = explode('.', $cookie, 2)[0];
    if (preg_match('/^[a-f0-9]{24}$/', $selector)) {
        $stmt = $pdo->prepare('DELETE FROM remember_tokens WHERE selector = :selector');
        $stmt->execute(['selector' => $selector]);
    }
}