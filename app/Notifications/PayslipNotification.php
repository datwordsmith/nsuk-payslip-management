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
        return (new MailMessage)
                ->subject("Payslip for " . date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year)))
                ->line('Please find attached your payslip.')
                ->line('Thank you for using our application!')
                ->attach(storage_path('app/' . $this->file->path));
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
