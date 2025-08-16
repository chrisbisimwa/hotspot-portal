<?php

declare(strict_types=1);

namespace App\Domain\Alerting\Channels;

use App\Domain\Alerting\Contracts\AlertChannelInterface;
use App\Domain\Alerting\DTO\AlertMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailAlertChannel implements AlertChannelInterface
{
    public function send(AlertMessage $message): bool
    {
        try {
            $config = config('alerting.email');
            $to = $config['to'];
            $from = $config['from'];
            $subjectPrefix = $config['subject_prefix'];

            if (empty($to) || empty($from)) {
                Log::warning('Email alert configuration incomplete, skipping alert', [
                    'alert_code' => $message->code,
                    'severity' => $message->severity->value,
                ]);
                return true; // Graceful fallback
            }

            $subject = "{$subjectPrefix} [{$message->severity->label()}] {$message->title}";

            $emailBody = view('emails.alert', [
                'message' => $message,
                'subject' => $subject,
            ])->render();

            Mail::raw($emailBody, function ($mail) use ($to, $from, $subject) {
                $mail->to($to)
                     ->from($from)
                     ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email alert', [
                'alert_code' => $message->code,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}