<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteUserToProjectNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Project $project, private User $guest)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = "/api/project/".$this->project->id."/decision";
        return (new MailMessage)
            ->subject('Um novo convite foi recebido')
            ->greeting("Olá, ".$this->guest->name."!")
            ->line("Você foi convidado a participar do projeto ")
            ->action('Ingressar no Projeto', url($url."/accept"))
            ->line('Se você não quiser entrar, pode clicar aqui para recusar o convite:')
            ->line(url($url."/reject"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
