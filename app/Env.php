<?php
declare(strict_types=1);

/**
 * Env — lector minimalista de archivos .env (sin dependencias externas).
 *
 * Carga uno o varios archivos en orden; el último sobreescribe al anterior.
 * Eso permite tener:
 *   .env        → variables GLOBALES del proyecto (se comparten / versionan como .env.example)
 *   .env.local  → variables LOCALES de esta máquina (rutas, credenciales, debug)
 */
final class Env
{
    /** @var array<string,string> */
    private static array $vars = [];

    private static bool $loaded = false;

    /**
     * Carga los archivos indicados. Los archivos que no existen se ignoran.
     */
    public static function load(string ...$files): void
    {
        foreach ($files as $file) {
            if (!is_file($file) || !is_readable($file)) {
                continue;
            }

            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                continue;
            }

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                    continue;
                }
                if (!str_contains($line, '=')) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);

                $key = trim($key);
                if (str_starts_with($key, 'export ')) {
                    $key = trim(substr($key, 7));
                }
                if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $key)) {
                    continue;
                }

                self::$vars[$key] = self::cleanValue($value);
            }
        }

        self::$loaded = true;
    }

    /**
     * Quita comillas, comentarios al final de línea y resuelve escapes básicos.
     */
    private static function cleanValue(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $first = $value[0];
        $last  = $value[strlen($value) - 1];

        if (strlen($value) >= 2 && ($first === '"' || $first === "'") && $first === $last) {
            $value = substr($value, 1, -1);

            if ($first === '"') {
                $value = str_replace(['\\n', '\\r', '\\t', '\\"', '\\\\'], ["\n", "\r", "\t", '"', '\\'], $value);
            }

            return $value;
        }

        // Valor sin comillas: se corta en el primer " #" (comentario al final)
        $comment = strpos($value, ' #');
        if ($comment !== false) {
            $value = rtrim(substr($value, 0, $comment));
        }

        return $value;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$vars);
    }

    /**
     * Devuelve el valor convertido a bool / int / null cuando corresponde.
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, self::$vars)) {
            return $default;
        }

        $value = self::$vars[$key];

        return match (strtolower($value)) {
            'true',  '(true)'  => true,
            'false', '(false)' => false,
            'null',  '(null)'  => null,
            'empty', '(empty)' => '',
            default            => is_numeric($value) && !str_contains($value, ' ')
                ? (str_contains($value, '.') ? (float) $value : (int) $value)
                : $value,
        };
    }

    /** @return array<string,string> */
    public static function all(): array
    {
        return self::$vars;
    }

    public static function isLoaded(): bool
    {
        return self::$loaded;
    }
}
