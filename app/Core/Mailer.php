<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    public static function send(
        string $to,
        string $subject,
        string $body
    ): bool {
        $mail = new PHPMailer(true);

        try {
            $mail->isMail();

            $mail->CharSet = 'UTF-8';

            $mail->setFrom(
                'no-reply@arvand-audio-store.test',
                'Arvand Audio Store'
            );

            $mail->addAddress($to);

            $mail->isHTML(true);

            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    public static function sendVerificationCode(
        string $email,
        string $name,
        string $code
    ): bool {
        $body = "
            <div style=\"font-family:Arial,sans-serif;direction:rtl\">
                <h2>فروشگاه آروند</h2>

                <p>کاربر گرامی {$name}</p>

                <p>کد تأیید ایمیل شما:</p>

                <h1 style=\"letter-spacing:8px\">{$code}</h1>

                <p>
                    این کد تا ۱۰ دقیقه معتبر است.
                </p>

                <p>
                    اگر این درخواست توسط شما انجام نشده است،
                    می‌توانید این ایمیل را نادیده بگیرید.
                </p>
            </div>
        ";

        $result = self::send(
            $email,
            'کد تأیید ایمیل فروشگاه آروند',
            $body
        );

        /*
         * Development log
         *
         * این بخش فقط برای تست محیط توسعه است.
         * بعداً می‌توانیم آن را با SMTP واقعی جایگزین کنیم.
         */
        $logDir = dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($logDir)) {
            mkdir(
                $logDir,
                0775,
                true
            );
        }

        file_put_contents(
            $logDir . '/mail.log',
            sprintf(
                "[%s] verification_code | email=%s | code=%s | sent=%s%s",
                date('Y-m-d H:i:s'),
                $email,
                $code,
                $result ? 'true' : 'false',
                PHP_EOL
            ),
            FILE_APPEND
        );

        return $result;
    }
}