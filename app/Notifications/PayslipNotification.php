<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayslipNotification extends Notification
{
    use Queueable;

    public $file;
    public $month;
    public $year;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($file, $month, $year)
    {
        $this->file = $file;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        \Log::info('PayslipNotification toMail called', [
            'recipient' => $notifiable->email,
            'file_path' => $this->file->path,
            'month' => $this->month,
            'year' => $this->year
        ]);

        $period = date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
        $subject = "Payslip for " . $period;

        $filePath = storage_path('app/' . $this->file->path);

        \Log::info('File attachment details', [
            'full_path' => $filePath,
            'file_exists' => file_exists($filePath),
            'file_size' => file_exists($filePath) ? filesize($filePath) : 'N/A',
            'is_readable' => is_readable($filePath)
        ]);

        try {
            $mailMessage = (new MailMessage)
                ->view(
                    'emails.payslip',
                    [
                        'subject' => $subject,
                        'period' => $period
                    ]
                )
                ->subject($subject)
                ->attach($filePath);

            \Log::info('Mail message created successfully');
            return $mailMessage;

        } catch (\Exception $e) {
            \Log::error('Error creating mail message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
