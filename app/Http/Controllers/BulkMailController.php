<?php

namespace App\Http\Controllers;

use App\Models\BulkMailing;
use App\Models\EmailTemplate;
use App\Models\MailConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class BulkMailController extends Controller
{
    public const MAX_RECIPIENTS = 500;

    public function index()
    {
        $mailings = BulkMailing::query()
            ->where('user_id', auth()->id())
            ->with(['emailTemplate', 'mailConfiguration'])
            ->latest()
            ->paginate(9);

        return view('dashboard.bulk-mail.index', compact('mailings'));
    }

    public function create()
    {
        $mailConfigurations = MailConfiguration::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('is_default', 'desc')
            ->get();

        $templates = EmailTemplate::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        $templateOptions = $templates->map(fn (EmailTemplate $template) => [
            'id' => $template->id,
            'name' => $template->name,
            'subject' => $template->subject,
            'content' => $template->content,
        ])->values();

        $configurationOptions = $mailConfigurations->map(fn (MailConfiguration $config) => [
            'id' => $config->id,
            'from_name' => $config->from_name,
            'from_email' => $config->from_email,
            'reply_to' => $config->reply_to_email,
            'host' => $config->smtp_host,
            'is_default' => $config->is_default,
        ])->values();

        return view('dashboard.bulk-mail.create', compact('mailConfigurations', 'templates', 'templateOptions', 'configurationOptions'));
    }

    public function parseContacts(Request $request): JsonResponse
    {
        $request->validate([
            'source' => ['required', 'in:paste,file'],
            'input' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:4096'],
        ]);

        $source = $request->input('source');

        if ($source === 'file' && $request->hasFile('file')) {
            $raw = $request->file('file')->getContent();
        } elseif ($source === 'paste') {
            $raw = (string) $request->input('input', '');
        } else {
            return response()->json(['message' => 'No contact source provided.'], 422);
        }

        $emails = $this->extractEmails($raw);

        return response()->json([
            'emails' => $emails,
            'total' => count($emails),
        ]);
    }

    public function send(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'mail_configuration_id' => ['required', 'integer'],
            'email_template_id' => ['required', 'integer'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['required', 'email'],
        ]);

        $mailConfiguration = MailConfiguration::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($data['mail_configuration_id']);

        $template = EmailTemplate::query()
            ->where('user_id', auth()->id())
            ->findOrFail($data['email_template_id']);

        $recipients = array_values(array_unique(array_map(
            'strtolower',
            array_filter($data['recipients'], fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
        )));

        $recipients = array_slice($recipients, 0, self::MAX_RECIPIENTS);

        if (count($recipients) === 0) {
            return back()->withErrors(['recipients' => 'No valid recipient email addresses were provided.']);
        }

        $this->applyMailConfiguration($mailConfiguration);

        $subject = $this->personalize($template->subject ?? '', $recipients[0]);

        $mailing = BulkMailing::create([
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()->tenant_id,
            'mail_configuration_id' => $mailConfiguration->id,
            'email_template_id' => $template->id,
            'subject' => $subject,
            'recipients' => $recipients,
            'recipients_count' => count($recipients),
            'status' => BulkMailing::STATUS_PROCESSING,
        ]);

        $results = [];
        $failures = [];

        foreach ($recipients as $email) {
            try {
                $html = $this->personalize($template->content, $email);

                Mail::mailer('smtp')->html($html, function ($message) use ($mailConfiguration, $subject, $email) {
                    $message
                        ->to($email)
                        ->subject($subject)
                        ->from($mailConfiguration->from_email, $mailConfiguration->from_name ?? '');

                    if (! empty($mailConfiguration->reply_to_email)) {
                        $message->replyTo($mailConfiguration->reply_to_email);
                    }
                });

                $results[$email] = 'sent';
            } catch (Throwable $e) {
                $results[$email] = 'failed';
                $failures[$email] = $e->getMessage();
            }
        }

        $sent = count(array_filter($results, fn ($status) => $status === 'sent'));
        $failed = count($results) - $sent;

        $mailing->update([
            'results' => $results,
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => $failed === count($recipients) ? BulkMailing::STATUS_FAILED : BulkMailing::STATUS_COMPLETED,
            'error' => empty($failures) ? null : collect($failures)->implode("\n"),
        ]);

        $payload = [
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
            'message' => "Campaign sent to {$sent} recipient".($sent === 1 ? '' : 's').
                ($failed > 0 ? "; {$failed} email".($failed === 1 ? '' : 's').' failed to deliver.' : ''),
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        $response = back()->with('success', $payload['message']);

        if ($failed > 0) {
            $response->with('warning', $payload['message']);
        }

        return $response;
    }

    /**
     * @return array<int, string>
     */
    protected function extractEmails(string $raw): array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return [];
        }

        $candidates = [];

        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item)) {
                        $candidates[] = $item;
                    } elseif (is_array($item) || is_object($item)) {
                        $item = (array) $item;
                        foreach (['email', 'email_address', 'address'] as $key) {
                            if (isset($item[$key])) {
                                $candidates[] = $item[$key];
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (empty($candidates)) {
            $candidates = $this->extractCandidatesFromFlatText($raw);
        }

        $emails = [];
        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate, " \t\n\r\0\x0B\"'[]");
            $candidate = strtolower($candidate);

            if (! filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $emails[$candidate] = true;
        }

        return array_slice(array_keys($emails), 0, self::MAX_RECIPIENTS);
    }

    /**
     * @return array<int, string>
     */
    protected function extractCandidatesFromFlatText(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $candidates = [];

        $headerIndex = null;
        $emailColumn = null;

        foreach ($lines as $index => $line) {
            $cells = array_map('trim', explode(',', $line));
            if (count($cells) > 1 && str_contains(strtolower($line), 'email')) {
                $headerIndex = $index;
                foreach ($cells as $i => $cell) {
                    if (str_contains(strtolower($cell), 'email')) {
                        $emailColumn = $i;
                        break;
                    }
                }
                break;
            }
        }

        if ($headerIndex !== null) {
            foreach (array_slice($lines, $headerIndex + 1) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $cells = str_getcsv($line);
                if ($emailColumn !== null && isset($cells[$emailColumn])) {
                    $candidates[] = $cells[$emailColumn];
                } else {
                    $candidates[] = $line;
                }
            }

            return $candidates;
        }

        $tokens = preg_split('/[\s,;]+/', $raw) ?: [];
        $candidates = array_merge($candidates, $tokens);

        if (count(array_filter(array_map('trim', $tokens))) === 1 && str_contains($raw, '"')) {
            $candidates = str_getcsv($raw);
        }

        return $candidates;
    }

    public function sendTest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mail_configuration_id' => ['required', 'integer'],
        ]);

        $mailConfiguration = MailConfiguration::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($data['mail_configuration_id']);

        $recipient = auth()->user()->email;

        try {
            $this->applyMailConfiguration($mailConfiguration);

            Mail::mailer('smtp')->html($this->testMailHtml(), function ($message) use ($mailConfiguration, $recipient) {
                $message
                    ->to($recipient)
                    ->subject('Test email from '.($mailConfiguration->from_name ?: $mailConfiguration->from_email))
                    ->from($mailConfiguration->from_email, $mailConfiguration->from_name ?? '');

                if (! empty($mailConfiguration->reply_to_email)) {
                    $message->replyTo($mailConfiguration->reply_to_email);
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Test email sent to {$recipient}.",
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not send test email: '.$e->getMessage(),
            ], 422);
        }
    }

    protected function testMailHtml(): string
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

    protected function applyMailConfiguration(MailConfiguration $mailConfiguration): void
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

    protected function personalize(string $content, string $email): string
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
}
