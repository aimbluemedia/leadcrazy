<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Image uploads for logos, hero banners and gallery photos.
 *
 * Files land in public/uploads/{account id}/ under a random name with an
 * extension chosen by us from the detected image type, never from the name the
 * browser sent. public/uploads/.htaccess switches PHP off in there as well, so
 * a file that somehow passed as an image still cannot be executed.
 */
final class Uploads
{
    public const MAX_BYTES = 6 * 1024 * 1024;

    private const TYPES = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF  => 'gif',
    ];

    /**
     * Saves one entry of $_FILES. Returns ['path' => '/uploads/..'] or ['error' => '...'].
     *
     * @param array<string,mixed> $file
     * @return array{path?:string,error?:string}
     */
    public static function image(array $file, int $accountId): array
    {
        $code = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($code === UPLOAD_ERR_NO_FILE) {
            return ['error' => 'No file was chosen.'];
        }
        if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'That image is too large. The limit is 6 MB.'];
        }
        if ($code !== UPLOAD_ERR_OK) {
            return ['error' => 'The upload did not finish. Please try again.'];
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return ['error' => 'The upload did not finish. Please try again.'];
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            return ['error' => 'That image is too large. The limit is 6 MB.'];
        }

        $info = @getimagesize($tmp);
        $ext = is_array($info) ? (self::TYPES[$info[2]] ?? null) : null;
        if ($ext === null) {
            return ['error' => 'Only JPG, PNG, WebP or GIF images can be uploaded.'];
        }

        $dir = PUBLIC_PATH . '/uploads/' . $accountId;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return ['error' => 'The uploads folder is not writable. Check permissions on public/uploads.'];
        }

        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
            return ['error' => 'The image could not be saved. Check permissions on public/uploads.'];
        }

        return ['path' => '/uploads/' . $accountId . '/' . $name];
    }

    /**
     * $_FILES for a multiple input (name="photos[]") as a list of single files.
     *
     * @return list<array<string,mixed>>
     */
    public static function many(string $field): array
    {
        $raw = $_FILES[$field] ?? null;
        if (!is_array($raw) || !is_array($raw['name'] ?? null)) {
            return [];
        }
        $out = [];
        foreach (array_keys($raw['name']) as $i) {
            if ((int) ($raw['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = [
                'name' => $raw['name'][$i], 'type' => $raw['type'][$i] ?? '',
                'tmp_name' => $raw['tmp_name'][$i] ?? '', 'error' => $raw['error'][$i] ?? 0,
                'size' => $raw['size'][$i] ?? 0,
            ];
        }
        return $out;
    }

    /** Deletes a file this class saved. Anything else is left alone. */
    public static function delete(?string $path): void
    {
        if (!is_string($path) || !preg_match('#^/uploads/\d+/[a-f0-9]{24}\.(jpg|png|webp|gif)$#', $path)) {
            return;
        }
        $file = PUBLIC_PATH . $path;
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
