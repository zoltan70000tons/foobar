<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Sends a test email to the specified address to verify SMTP configuration.';

    public function handle(): int
    {
        $email = $this->argument('email');

        $mailer = Config::get('mail.default');
        $mailConfig = Config::get("mail.mailers.$mailer", []);

        $transport   = $mailConfig['transport'] ?? 'N/A';
        $host        = $mailConfig['host'] ?? 'N/A';
        $port        = $mailConfig['port'] ?? 'N/A';
        $encryption  = $mailConfig['encryption'] ?? 'N/A';
        $fromAddress = Config::get('mail.from.address', 'N/A');
        $fromName    = Config::get('mail.from.name', 'N/A');

        $appName     = Config::get('app.name', 'N/A');
        $appEnv      = Config::get('app.env', 'N/A');
        $appUrl      = Config::get('app.url', 'N/A');
        $hostname    = gethostname() ?: php_uname('n');
        $ipAddress   = gethostbyname($hostname);
        $timestamp   = now()->toDateTimeString();

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SMTP Test Email</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5;">
    <p>This is a test email sent from Booking Admin Panel to verify the SMTP configuration.</p>
    <p style="color: green;"><strong>If you received this message, your email system is working correctly.</strong></p>

    <h3>Mail settings:</h3>
    <ul>
        <li><strong>Mailer:</strong> {$mailer}</li>
        <li><strong>Transport:</strong> {$transport}</li>
        <li><strong>Host:</strong> {$host}</li>
        <li><strong>Port:</strong> {$port}</li>
        <li><strong>Encryption:</strong> {$encryption}</li>
    </ul>

    <h3>From:</h3>
    <ul>
        <li><strong>Name:</strong> {$fromName}</li>
        <li><strong>Address:</strong> {$fromAddress}</li>
    </ul>

    <h3>Application info:</h3>
    <ul>
        <li><strong>App Name:</strong> {$appName}</li>
        <li><strong>Environment:</strong> {$appEnv}</li>
        <li><strong>App URL:</strong> {$appUrl}</li>
    </ul>

    <h3>Server info:</h3>
    <ul>
        <li><strong>Hostname:</strong> {$hostname}</li>
        <li><strong>IP Address:</strong> {$ipAddress}</li>
        <li><strong>Sent at:</strong> {$timestamp}</li>
    </ul>
</body>
</html>
HTML;

        try {
            Mail::html($html, function ($message) use ($email) {
                $message->to($email)
                        ->subject('SMTP Test Email from Booking Admin Panel');
            });

            $this->info("Test email successfully sent to {$email}");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
