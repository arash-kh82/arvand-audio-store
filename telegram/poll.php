<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Controllers\TelegramController;
use App\Services\TelegramService;

$telegram = new TelegramService();
$controller = new TelegramController();

echo 'Controller class: '
    . get_class($controller)
    . PHP_EOL;

echo 'Controller file: '
    . (new ReflectionClass($controller))->getFileName()
    . PHP_EOL;

echo 'processUpdate exists: '
    . (method_exists($controller, 'processUpdate') ? 'YES' : 'NO')
    . PHP_EOL;

$offset = 0;

echo "Telegram polling started..." . PHP_EOL;

while (true) {
    try {
        $response = $telegram->getUpdates(
            $offset,
            10
        );

        $updates = $response['result'] ?? [];

        if (!is_array($updates)) {
            continue;
        }

        foreach ($updates as $update) {
            if (!is_array($update)) {
                continue;
            }

            $updateId = (int) (
                $update['update_id']
                ?? 0
            );

            if ($updateId > 0) {
                $offset = $updateId + 1;
            }

            $controller->processUpdate(
                $update
            );
        }
    } catch (Throwable $exception) {

        echo
        '[ERROR] '
            . $exception->getMessage()
            . PHP_EOL;

        sleep(3);
    }
}
