<?php

namespace App\Infrastructure\Settings;

use App\Domain\Settings\Contracts\MailTester;
use App\Domain\Settings\Data\MailTestResult;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Mail\MailManager;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Throwable;

/**
 * A real send through the SMTP mailer in force, skipping the queue, so the
 * admin reads the server's own answer ("535 authentication failed") on the
 * panel instead of in a worker log.
 */
final readonly class SmtpMailTester implements MailTester
{
    private const PROBLEMS = [
        'auth' => '/\b(530|534|535)\b|authenticat|username and password/i',
        'certificate' => '/certificate|ssl|tls|crypto/i',
        'connection' => '/could not be established|connection refused|timed out|getaddrinfo|resolve|unreachable/i',
        'sender' => '/\b55[0-4]\b|sender|from address/i',
    ];

    public function __construct(private MailManager $mail, private Config $config) {}

    public function send(array $to, string $subject, string $body): MailTestResult
    {
        // A transport built before this request's coordinates would test the old server.
        $this->mail->purge('smtp');

        try {
            $this->mail->mailer('smtp')->raw($body, fn (Message $message) => $message->to($to)->subject($subject));
        } catch (Throwable $e) {
            $detail = $this->scrub($e->getMessage());

            return new MailTestResult(false, self::classify($detail), Str::limit($detail, 300));
        }

        return new MailTestResult(true);
    }

    /** The server echoes what it was sent: never let the user or the password out. */
    private function scrub(string $message): string
    {
        $secrets = array_filter([
            (string) $this->config->get('mail.mailers.smtp.password'),
            (string) $this->config->get('mail.mailers.smtp.username'),
        ], fn (string $s) => strlen($s) >= 3);
        foreach ($secrets as $secret) {
            $message = str_ireplace([$secret, base64_encode($secret)], '***', $message);
        }
        // An echoed AUTH payload carries user and password together in base64: drop any such blob.
        $message = (string) preg_replace('/(?=[A-Za-z0-9+\/]*\d)(?=[A-Za-z0-9+\/]*[A-Z])[A-Za-z0-9+\/]{12,}={0,2}(\*\*\*)?/', '***', $message);

        return trim((string) preg_replace('/\s+/', ' ', $message));
    }

    private static function classify(string $detail): string
    {
        foreach (self::PROBLEMS as $problem => $pattern) {
            if (preg_match($pattern, $detail) === 1) {
                return $problem;
            }
        }

        return 'other';
    }
}
