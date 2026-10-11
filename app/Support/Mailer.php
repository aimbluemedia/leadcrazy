<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Sends plain-text email.
 *
 * driver 'smtp' sends through the mailbox in config mail.smtp (Hostinger:
 * smtp.hostinger.com, port 465, ssl). driver 'log' writes the message to
 * storage/logs/mail.log instead -- what a fresh install does until SMTP is
 * filled in, so a missing mailbox is visible rather than silently broken.
 * Left empty, the driver is 'smtp' when a host and password are set, else 'log'.
 */
final class Mailer
{
    /** @return array{ok:bool,error:?string} */
    public static function send(string $to, string $subject, string $text): array
    {
        $to = trim($to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Not a valid email address.'];
        }

        $fromEmail = trim((string) Config::get('mail.from_email', '')) ?: trim((string) Config::get('mail.smtp.username', ''));
        $fromName = trim((string) Config::get('mail.from_name', 'LeadCrazy')) ?: 'LeadCrazy';
        $fromHeader = $fromEmail !== '' ? self::headerName($fromName) . ' <' . $fromEmail . '>' : $fromName;
        $replyTo = trim((string) Config::get('support_email', ''));

        if (self::driver() === 'smtp') {
            $result = Smtp::send([
                'to' => $to,
                'subject' => $subject,
                'text' => $text,
                'from_header' => $fromHeader,
                'reply_to' => $replyTo !== '' && $replyTo !== $fromEmail ? $replyTo : null,
            ]);
            if (!$result['ok']) {
                ErrorHandler::note('mail', 'could not send "' . $subject . '" to ' . $to . ': ' . $result['error']);
            }
            return ['ok' => $result['ok'], 'error' => $result['error']];
        }

        $line = sprintf("[%s] To: %s | From: %s | Subject: %s\n%s\n\n", date('Y-m-d H:i:s'), $to, $fromHeader, $subject, $text);
        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($dir . '/mail.log', $line, FILE_APPEND | LOCK_EX);
        return ['ok' => true, 'error' => null];
    }

    public static function driver(): string
    {
        $driver = strtolower(trim((string) Config::get('mail.driver', '')));
        if (in_array($driver, ['smtp', 'log'], true)) {
            return $driver;
        }
        return trim((string) Config::get('mail.smtp.host', '')) !== '' && (string) Config::get('mail.smtp.password', '') !== ''
            ? 'smtp' : 'log';
    }

    private static function headerName(string $name): string
    {
        $name = str_replace(['"', "\r", "\n"], '', $name);
        return preg_match('/[^\x20-\x7E]/', $name) ? '=?UTF-8?B?' . base64_encode($name) . '?=' : '"' . $name . '"';
    }
}
