<?php

namespace App\Jobs;

use App\Models\BulkMailing;
use App\Models\BulkMailingResult;
use App\Support\BulkMailer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendBulkEmailBatch implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    /**
     * @param  array<int, string>  $emails
     */
    public function __construct(
        public int $bulkMailingId,
        public array $emails,
    ) {}

    public function handle(BulkMailer $mailer): void
    {
        $mailing = BulkMailing::with(['mailConfiguration', 'emailTemplate'])
            ->find($this->bulkMailingId);

        if (! $mailing || ! $mailing->mailConfiguration || ! $mailing->emailTemplate) {
            foreach ($this->emails as $email) {
                BulkMailingResult::create([
                    'bulk_mailing_id' => $this->bulkMailingId,
                    'email' => $email,
                    'status' => BulkMailingResult::STATUS_FAILED,
                    'error' => 'Mail configuration or email template is no longer available.',
                ]);
            }

            return;
        }

        $mailConfiguration = $mailing->mailConfiguration;
        $template = $mailing->emailTemplate;
        $subject = $mailing->subject ?? $mailer->personalize($template->subject ?? '', $this->emails[0]);

        foreach ($this->emails as $email) {
            try {
                $html = $mailer->personalize($template->content, $email);
                $mailer->send($mailConfiguration, $subject, $html, $email);

                BulkMailingResult::create([
                    'bulk_mailing_id' => $this->bulkMailingId,
                    'email' => $email,
                    'status' => BulkMailingResult::STATUS_SENT,
                ]);
            } catch (Throwable $e) {
                BulkMailingResult::create([
                    'bulk_mailing_id' => $this->bulkMailingId,
                    'email' => $email,
                    'status' => BulkMailingResult::STATUS_FAILED,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
