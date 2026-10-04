<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
    /** @param array<string,mixed> $data */
    public static function render(string $template, array $data = []): string
    {
        $file = APP_ROOT . '/Views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    /** Renders a view inside the marketing layout. @param array<string,mixed> $data */
    public static function page(string $template, array $data = []): string
    {
        return self::wrap('layout', $template, $data);
    }

    /** @param array<string,mixed> $data */
    public static function superadmin(string $template, array $data = []): string
    {
        return self::wrap('superadmin/layout', $template, $data + ['title' => 'Superadmin']);
    }

    /** @param array<string,mixed> $data */
    public static function members(string $template, array $data = []): string
    {
        return self::wrap('members/layout', $template, $data + ['title' => 'LeadCrazy']);
    }

    /** @param array<string,mixed> $data */
    private static function wrap(string $layout, string $template, array $data): string
    {
        $content = self::render($template, $data);
        return self::render($layout, $data + [
            'content' => $content,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'current' => $data['current'] ?? null,
        ]);
    }

    /** Escape for HTML output. Every dynamic value in a template goes through this. */
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Plain text with line breaks kept, escaped. */
    public static function text(?string $value): string
    {
        return nl2br(self::e($value), false);
    }

    /**
     * A web-root-relative asset URL with the file's modification time on it, so
     * a browser holding last week's stylesheet fetches the new one after an
     * upload. Same approach as PromoMonster.
     */
    public static function asset(string $path): string
    {
        static $cache = [];

        if (!isset($cache[$path])) {
            $file = (defined('PUBLIC_PATH') ? PUBLIC_PATH : '') . $path;
            $mtime = is_file($file) ? @filemtime($file) : false;
            $cache[$path] = $mtime === false ? $path : $path . '?v=' . $mtime;
        }

        return $cache[$path];
    }

    /** Absolute URL on this site, for embed code and the MonsterList feed. */
    public static function url(string $path = '/'): string
    {
        return rtrim((string) Config::get('app_url', ''), '/') . '/' . ltrim($path, '/');
    }

    /** The session flash for an area, read once. */
    public static function flash(string $key): ?string
    {
        $value = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return is_string($value) && $value !== '' ? $value : null;
    }
}
