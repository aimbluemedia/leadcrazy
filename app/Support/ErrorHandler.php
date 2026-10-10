<?php

declare(strict_types=1);

namespace App\Support;

use Throwable;

/**
 * Turns a bare 500 into something diagnosable.
 *
 * Every uncaught error is written in full to storage/logs/error.log with a
 * short reference. In production the visitor sees only that reference; with
 * debug on, the detail is printed. Either way the error stops being invisible,
 * which is the whole problem with a default 500 on shared hosting.
 */
final class ErrorHandler
{
    private static bool $debug = false;

    public static function register(bool $debug): void
    {
        self::$debug = $debug;

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            // Respect the current error_reporting level (the @ operator).
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);

        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }
            self::handle(new \ErrorException(
                $error['message'], 0, $error['type'], $error['file'], $error['line'],
            ));
        });
    }

    public static function handle(Throwable $e): void
    {
        $reference = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        self::write($reference, $e);

        // On the command line there is no browser to render for, and a page of
        // HTML in a terminal hides the very message the operator needs.
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, "\nERROR {$reference}: " . get_class($e) . ': ' . $e->getMessage()
                . "\n  at " . $e->getFile() . ':' . $e->getLine() . "\n");
            return;
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        if (self::$debug || self::viewerIsStaff()) {
            echo '<pre style="font:13px ui-monospace,Menlo,monospace;padding:1.5rem;'
                . 'background:#fbe6da;color:#7a2d12;white-space:pre-wrap;">';
            echo 'Reference ' . $reference . "\n\n";
            echo htmlspecialchars(get_class($e) . ': ' . $e->getMessage(), ENT_QUOTES) . "\n";
            echo htmlspecialchars($e->getFile() . ':' . $e->getLine(), ENT_QUOTES) . "\n\n";
            echo htmlspecialchars($e->getTraceAsString(), ENT_QUOTES);
            echo '</pre>';
            return;
        }

        // Can't reach the database at all: say so, because the fix is in
        // config.php, not in the code, and the owner can't sign in to read
        // the log while the database is down. Never includes credentials.
        $dbHint = self::databaseHint($e);
        if ($dbHint !== null) {
            echo '<!doctype html><meta charset="utf-8"><title>Database connection problem</title>'
                . '<div style="font:16px system-ui,sans-serif;max-width:36rem;margin:12vh auto;padding:0 1.5rem;">'
                . '<h1 style="font-size:1.4rem;">The site can&rsquo;t reach its database</h1>'
                . '<p style="color:#5a6b7c;">' . $dbHint . '</p>'
                . '<p style="color:#5a6b7c;">Reference <strong>' . $reference . '</strong>.</p></div>';
            return;
        }

        echo '<!doctype html><meta charset="utf-8">'
            . '<title>Something went wrong</title>'
            . '<div style="font:16px system-ui,sans-serif;max-width:34rem;margin:12vh auto;padding:0 1.5rem;">'
            . '<h1 style="font-size:1.4rem;">Something went wrong</h1>'
            . '<p style="color:#5a6b7c;">We have logged it. If you are the site owner, '
            . 'sign in at <code>/superadmin/errors</code> and search for reference '
            . '<strong>' . $reference . '</strong>.</p></div>';
    }

    /** A plain-language hint for database connection failures, or null. */
    private static function databaseHint(Throwable $e): ?string
    {
        if (!$e instanceof \PDOException) {
            return null;
        }
        $code = (int) ($e->errorInfo[1] ?? 0) ?: (int) preg_replace('/\D.*/', '', (string) preg_replace('/^.*?\[(\d+)\].*$/s', '$1', $e->getMessage()));
        return match ($code) {
            1045 => 'The database username or password in <code>app/config.php</code> is wrong. '
                . 'Copy them exactly from your hosting panel (Databases &rarr; MySQL) &mdash; or reset the password there and paste the new one.',
            1044, 1049 => 'The database name in <code>app/config.php</code> is wrong, or that user has no access to it. '
                . 'Check the database name and that the user is assigned to it in your hosting panel.',
            2002, 2003, 2005 => 'The database server could not be reached. Check <code>host</code> in <code>app/config.php</code> '
                . '(on most shared hosting it is <code>localhost</code>).',
            default => null,
        };
    }

    /**
     * True when the person looking at the error is signed in as superadmin.
     * They get the detail on the page instead of a reference to look up;
     * everybody else still sees only the reference. Any failure here (the
     * database may be the very thing that is broken) means "no".
     */
    private static function viewerIsStaff(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['admin_user_id'])) {
            return false;
        }
        try {
            return Auth::isStaff();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Records something that went wrong but did not throw.
     *
     * A failed send is the case this exists for. It is not an exception -- the
     * mailer returns failure so the caller can carry on -- but it is exactly
     * the kind of thing somebody stares at a working-looking page wondering
     * about. error_log() was not enough: that goes to the server's log, which
     * is not the file the operator reads, so the one place anybody actually
     * looks stayed empty while sends failed.
     *
     * Same format as a crash entry, so the same reader picks it up.
     */
    public static function note(string $what, string $detail): void
    {
        $line = sprintf(
            "[%s] %-8s %s: %s%s",
            date('Y-m-d H:i:s'),
            'NOTE',
            $what,
            str_replace(["\r", "\n"], ' ', $detail),
            PHP_EOL . PHP_EOL,
        );

        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (@file_put_contents($dir . '/error.log', $line, FILE_APPEND | LOCK_EX) === false) {
            error_log('leadcrazy ' . $what . ': ' . $detail);
        }
    }

    private static function write(string $reference, Throwable $e): void
    {
        $line = sprintf(
            "[%s] %s  %s: %s  in %s:%d%s%s%s",
            date('Y-m-d H:i:s'),
            $reference,
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            PHP_EOL,
            $e->getTraceAsString(),
            PHP_EOL . PHP_EOL,
        );

        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        // Fall back to the server's own log if storage is not writable, so the
        // detail is never simply lost.
        if (@file_put_contents($dir . '/error.log', $line, FILE_APPEND | LOCK_EX) === false) {
            error_log('leadcrazy ' . $reference . ': ' . $e->getMessage()
                . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
