<?php
/**
 * Выход пользователя. Поддерживает GET (ссылка) — редиректит на главную.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

logout_user();

header('Location: ' . url('index.php'));
exit;
