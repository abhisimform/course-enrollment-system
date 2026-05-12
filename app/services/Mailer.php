<?php

require_once BASE_PATH . '/PHPMailer-master/src/Exception.php';
require_once BASE_PATH . '/PHPMailer-master/src/PHPMailer.php';
require_once BASE_PATH . '/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
  public static function sendWelcomeCredentials(array $recipient, array $account): array
  {
    $fromAddress = MAIL_FROM_ADDRESS !== '' ? MAIL_FROM_ADDRESS : MAIL_USERNAME;

    if (MAIL_HOST === '' || MAIL_USERNAME === '' || $fromAddress === '') {
      return [
        'sent' => false,
        'message' => 'SMTP settings are incomplete. Welcome email was not sent.'
      ];
    }

    $subject = 'Welcome to Course Enrollment System';
    $loginUrl = self::buildAbsoluteUrl('/auth/login');
    $htmlBody = self::renderTemplate('emails/account_welcome', [
      'recipientName' => $recipient['name'],
      'roleLabel' => ucfirst($account['role']),
      'loginUrl' => $loginUrl,
      'email' => $recipient['email'],
      'password' => $account['password']
    ]);

    $plainBody = self::buildPlainTextBody($recipient, $account, $loginUrl);

    try {
      $mailer = new PHPMailer(true);
      $mailer->isSMTP();
      $mailer->Host = MAIL_HOST;
      $mailer->Port = (int)MAIL_PORT;
      $mailer->SMTPAuth = true;
      $mailer->Username = MAIL_USERNAME;
      $mailer->Password = MAIL_PASSWORD;
      $mailer->CharSet = 'UTF-8';

      if (MAIL_ENCRYPTION === 'tls') {
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      } elseif (MAIL_ENCRYPTION === 'ssl') {
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      }

      $mailer->setFrom($fromAddress, MAIL_FROM_NAME);
      $mailer->addAddress($recipient['email'], $recipient['name']);
      $mailer->isHTML(true);
      $mailer->Subject = $subject;
      $mailer->Body = $htmlBody;
      $mailer->AltBody = $plainBody;

      $mailer->send();

      return [
        'sent' => true,
        'message' => 'Welcome email sent successfully.'
      ];
    } catch (Exception $exception) {
      error_log('Welcome email failed: ' . $exception->getMessage());

      return [
        'sent' => false,
        'message' => 'Account created, but the welcome email could not be sent.'
      ];
    }
  }

  private static function renderTemplate(string $viewPath, array $data): string
  {
    $view = BASE_PATH . '/views/' . $viewPath . '.php';

    if (!file_exists($view)) {
      return '';
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $view;
    return (string)ob_get_clean();
  }

  private static function buildAbsoluteUrl(string $path): string
  {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . $path;
  }

  private static function buildPlainTextBody(array $recipient, array $account, string $loginUrl): string
  {
    return "Hello {$recipient['name']},\n\n"
      . "Welcome to Course Enrollment System.\n\n"
      . "An administrator has created your {$account['role']} account.\n"
      . "Please use these temporary credentials to sign in:\n\n"
      . "Login URL: {$loginUrl}\n"
      . "Email: {$recipient['email']}\n"
      . "Temporary Password: {$account['password']}\n\n"
      . "Please log in and change your password immediately.\n\n"
      . "Regards,\n"
      . MAIL_FROM_NAME;
  }
}
