<?php
/**
 * Вспомогательные функции общего назначения.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Экранирование для вывода в HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Абсолютный URL внутри сайта.
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Транслитерация + слаг для тем/категорий.
 */
function slugify(string $text): string
{
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh',
        'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
        'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'ts',
        'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
    ];
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    $text = trim((string)$text, '-');
    if ($text === '') {
        $text = 'topic';
    }
    return mb_substr($text, 0, 120, 'UTF-8');
}

/**
 * Человеко-понятное «N времени назад».
 */
function time_ago(string $datetime): string
{
    $ts   = strtotime($datetime);
    $diff = time() - $ts;

    if ($diff < 0)      { return 'только что'; }
    if ($diff < 60)     { return 'только что'; }
    if ($diff < 3600)   { $m = (int)floor($diff / 60);   return $m . ' ' . plural($m, 'минуту', 'минуты', 'минут') . ' назад'; }
    if ($diff < 86400)  { $h = (int)floor($diff / 3600); return $h . ' ' . plural($h, 'час', 'часа', 'часов') . ' назад'; }
    if ($diff < 2592000){ $d = (int)floor($diff / 86400);return $d . ' ' . plural($d, 'день', 'дня', 'дней') . ' назад'; }

    return date('d.m.Y', $ts);
}

/**
 * Русские множественные формы.
 */
function plural(int $n, string $one, string $few, string $many): string
{
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $many;
    if ($n1 > 1 && $n1 < 5) return $few;
    if ($n1 === 1)          return $one;
    return $many;
}

/**
 * Цвет аватара по строке (детерминированно).
 */
function avatar_initial(string $username): string
{
    return mb_strtoupper(mb_substr($username, 0, 1, 'UTF-8'), 'UTF-8');
}

/**
 * Простой и безопасный рендер тела сообщения:
 *  - экранируем HTML
 *  - блоки ```code``` и `inline`
 *  - переносы строк -> <br> / абзацы
 *  - автоссылки
 */
function render_body(string $body): string
{
    $body = trim($body);

    // Уникальный токен-разделитель: только hex-символы, которые не
    // экранируются htmlspecialchars и не встретятся в пользовательском тексте.
    $token = 'CBLK' . bin2hex(random_bytes(8));

    // Вынимаем блоки кода, чтобы не трогать их остальными правилами.
    $codeBlocks = [];
    $body = preg_replace_callback('/```([\s\S]*?)```/', function ($m) use (&$codeBlocks, $token) {
        $idx = count($codeBlocks);
        $codeBlocks[$idx] = '<pre class="code-block"><code>' . e(trim($m[1], "\n")) . '</code></pre>';
        return "\n\n{$token}{$idx}{$token}\n\n";
    }, $body);

    $body = e($body);

    // inline `code`
    $body = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $body);

    // **жирный** и *курсив*
    $body = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $body);
    $body = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $body);

    // автоссылки
    $body = preg_replace(
        '#(https?://[^\s<]+)#',
        '<a href="$1" target="_blank" rel="noopener nofollow">$1</a>',
        $body
    );

    // абзацы / переносы
    $paragraphs = preg_split('/\n{2,}/', $body);
    $body = implode('', array_map(
        fn($p) => '<p>' . nl2br(trim($p)) . '</p>',
        array_filter($paragraphs, fn($p) => trim($p) !== '')
    ));

    // Сначала вынимаем плейсхолдер из обёртки <p>…</p>, чтобы <pre> не оказался
    // вложенным в абзац, затем возвращаем сами блоки кода.
    $body = preg_replace('#<p>(' . $token . '\d+' . $token . ')</p>#', '$1', $body);
    $body = preg_replace_callback(
        '/' . $token . '(\d+)' . $token . '/',
        fn($m) => $codeBlocks[(int)$m[1]] ?? '',
        $body
    );

    return $body;
}

/**
 * Отправить JSON-ответ и завершить выполнение.
 */
function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * JSON-ошибка.
 */
function json_error(string $message, int $status = 400): never
{
    json_response(['ok' => false, 'error' => $message], $status);
}
