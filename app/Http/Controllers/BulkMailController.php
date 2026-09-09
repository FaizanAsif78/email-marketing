<?php

namespace App\Http\Controllers;

use App\Jobs\SendBulkEmailBatch;
use App\Models\BulkMailing;
use App\Models\EmailTemplate;
use App\Models\MailConfiguration;
use App\Support\BulkMailer;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Throwable;

class BulkMailController extends Controller
{
    public const MAX_RECIPIENTS = 500;

    public const BATCH_SIZE = 10;

    public function index()
    {
        $mailings = BulkMailing::query()
            ->where('user_id', auth()->id())
            ->with(['emailTemplate', 'mailConfiguration', 'deliveries'])
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

        $mailer = app(BulkMailer::class);

        $mailing = BulkMailing::create([
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()->tenant_id,
            'mail_configuration_id' => $mailConfiguration->id,
            'email_template_id' => $template->id,
            'subject' => $mailer->personalize($template->subject ?? '', $recipients[0]),
            'recipients' => $recipients,
            'recipients_count' => count($recipients),
            'status' => BulkMailing::STATUS_PROCESSING,
        ]);

        $mailingId = $mailing->id;
        $chunks = array_chunk($recipients, self::BATCH_SIZE);
        $jobs = array_map(fn (array $emails) => new SendBulkEmailBatch($mailingId, $emails), $chunks);

        $batch = Bus::batch($jobs)
            ->name("Bulk mailing #{$mailingId} ({$mailing->recipients_count} emails, ".count($jobs).' batches of '.self::BATCH_SIZE.')')
            ->allowFailures()
            ->then(function (Batch $batch) use ($mailingId) {
                BulkMailing::finalizeResults($mailingId);
            })
            ->dispatch();

        $mailing->update(['job_batch_id' => $batch->id]);

        $total = $mailing->recipients_count;
        $batchCount = count($jobs);
        $payload = [
            'success' => true,
            'queued' => true,
            'total' => $total,
            'batch_id' => $batch->id,
            'batches' => $batchCount,
            'message' => "{$total} email".($total === 1 ? '' : 's')." queued for delivery in {$batchCount} batch".($batchCount === 1 ? '' : 'es').' of '.self::BATCH_SIZE.'.',
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', $payload['message']);
    }

    public function batchStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'string'],
        ]);

        $mailing = BulkMailing::query()
            ->where('user_id', auth()->id())
            ->where('job_batch_id', $validated['batch_id'])
            ->firstOrFail();

        $batch = Bus::findBatch($validated['batch_id']);

        return response()->json([
            'finished' => $mailing->status !== BulkMailing::STATUS_PROCESSING,
            'status' => $mailing->status,
            'total' => $mailing->recipients_count,
            'sent' => $mailing->sent_count,
            'failed' => $mailing->failed_count,
            'pending_jobs' => $batch ? $batch->pendingJobs : null,
        ]);
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
        $mailer = app(BulkMailer::class);

        try {
            $mailer->send(
                $mailConfiguration,
                'Test email from '.($mailConfiguration->from_name ?: $mailConfiguration->from_email),
                $mailer->testMailHtml(),
                $recipient,
            );

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
}
