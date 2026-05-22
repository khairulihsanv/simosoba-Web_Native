<?php
/**
 * classes/DbSession.php
 * TiDB/MySQL-backed PHP session handler.
 *
 * Why: Vercel serverless functions get an ephemeral /tmp on every cold start,
 * meaning file-based sessions are lost between the POST (login) and the
 * subsequent GET (dashboard). Storing sessions in TiDB fixes this for both
 * Vercel and any multi-instance deployment.
 */
class DbSession implements SessionHandlerInterface
{
    private PDO  $pdo;
    private int  $lifetime;
    private bool $ready = false;

    public function __construct(PDO $pdo)
    {
        $this->pdo      = $pdo;
        $this->lifetime = max((int) ini_get('session.gc_maxlifetime'), 7200);
    }

    /* ── Create table once per request ── */
    private function boot(): void
    {
        if ($this->ready) return;
        $this->ready = true;
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS php_sessions (
                    id         VARCHAR(128) NOT NULL,
                    data       MEDIUMTEXT   NOT NULL DEFAULT '',
                    expires_at DATETIME     NOT NULL,
                    PRIMARY KEY (id),
                    INDEX idx_exp (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Throwable $e) {
            // Table already exists or DB is read-only — safe to ignore
        }
    }

    public function open(string $path, string $name): bool
    {
        $this->boot();
        return true;
    }

    public function close(): bool { return true; }

    public function read(string $id): string|false
    {
        try {
            $this->boot();
            $stmt = $this->pdo->prepare(
                "SELECT data FROM php_sessions
                  WHERE id = ? AND expires_at > NOW()
                  LIMIT 1"
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (string) $row['data'] : '';
        } catch (Throwable $e) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $exp  = date('Y-m-d H:i:s', time() + $this->lifetime);
            $stmt = $this->pdo->prepare("
                INSERT INTO php_sessions (id, data, expires_at)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    data       = VALUES(data),
                    expires_at = VALUES(expires_at)
            ");
            $stmt->execute([$id, $data, $exp]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $this->pdo->prepare("DELETE FROM php_sessions WHERE id = ?")
                      ->execute([$id]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM php_sessions WHERE expires_at < NOW()"
            );
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
