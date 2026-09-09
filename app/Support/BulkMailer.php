<?php

namespace App\Support;

use App\Models\MailConfiguration;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BulkMailer
{
    public function configure(MailConfiguration $mailConfiguration): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $mailConfiguration->encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => $mailConfiguration->smtp_host,
            'mail.mailers.smtp.port' => $mailConfiguration->smtp_port,
            'mail.mailers.smtp.username' => $mailConfiguration->username,
            'mail.mailers.smtp.password' => $mailConfiguration->password,
            'mail.from.address' => $mailConfiguration->from_email,
            'mail.from.name' => $mailConfiguration->from_name,
        ]);
    }

    public function personalize(string $content, string $email): string
    {
        $localPart = Str::of($email)->before('@');
        $contactName = Str::of($localPart)->replace(['.', '_', '-'], ' ')->title();

        return strtr($content, [
            '{email}' => $email,
            '{email_address}' => $email,
            '{contact_name}' => $contactName->toString(),
            '{first_name}' => Str::of($contactName)->before(' ')->trim()->toString(),
        ]);
    }

    public function send(MailConfiguration $mailConfiguration, string $subject, string $html, string $email): void
    {
        $this->configure($mailConfiguration);

        Mail::mailer('smtp')->html($html, function ($message) use ($mailConfiguration, $subject, $email) {
            $message
                ->to($email)
                ->subject($subject)
                ->from($mailConfiguration->from_email, $mailConfiguration->from_name ?? '');

            if (! empty($mailConfiguration->reply_to_email)) {
                $message->replyTo($mailConfiguration->reply_to_email);
            }
        });
    }

    public function testMailHtml(): string
    {
        $appUrl = url('/');

        return <<<'HTML'
            <!DOCTYPE html>
            <html>
            <body style="margin:0;padding:0;background:#f4f5fb;font-family:Arial,sans-serif;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f5fb;padding:24px;">
                    <tr><td align="center">
                        <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;">
                            <tr><td style="background:#696cff;padding:24px 32px;">
                                <h2 style="margin:0;color:#ffffff;">SMTP Test Successful</h2>
                            </td></tr>
                            <tr><td style="padding:32px;">
                                <p style="margin:0 0 16px;color:#333;font-size:15px;line-height:1.6;">
                                    This is a test email sent from your email marketing application to confirm your
                                    SMTP configuration is working correctly.
                                </p>
                                <p style="margin:0 0 16px;color:#333;font-size:15px;line-height:1.6;">
                                    If you are reading this, your mail configuration is ready to send bulk campaigns.
                                </p>
                                <a href="{{ $appUrl }}" style="display:inline-block;background:#696cff;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;">Open App</a>
                            </td></tr>
                        </table>
                    </td></tr>
                </table>
            </body>
            </html>
        HTML;
    }
}
