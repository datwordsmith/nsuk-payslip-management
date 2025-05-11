<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserAlert extends Notification
{
    use Queueable;

    protected $userPassword;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($userPassword)
    {
        $this->userPassword = $userPassword;
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
                    ->subject('New Account Information')
                    ->line('Welcome to Trackr!')
                    ->line('You have now been added as a user on the SABC Trackr App')
                    ->line('Your unique password is:')
                    ->line($this->userPassword)
                    ->action('Click to login', url('/'));
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
