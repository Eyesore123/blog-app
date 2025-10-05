<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Events\MessageFailed;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Log successful emails with real recipients
        Event::listen(MessageSent::class, function (MessageSent $event) {
        // Try envelope first (Symfony Mailer)
        $recipients = $event->sent && $event->sent->getEnvelope()
            ? $event->sent->getEnvelope()->getRecipients()
            : ($event->message->getTo() ?? []);

        $to = collect($recipients)
            ->map(fn($addr) => method_exists($addr, 'toString') ? $addr->toString() : (string) $addr)
            ->all();

        $sender = $event->sent && $event->sent->getEnvelope() && $event->sent->getEnvelope()->getSender()
            ? [$event->sent->getEnvelope()->getSender()]
            : ($event->message->getFrom() ?? []);

        $from = collect($sender)
            ->map(fn($addr) => method_exists($addr, 'toString') ? $addr->toString() : (string) $addr)
            ->all();

        Log::info('Email SENT', [
            'from'    => $from,
            'to'      => $to,
            'subject' => $event->message->getSubject(),
        ]);
    });


        // Log failed emails
        Event::listen(MessageFailed::class, function (MessageFailed $event) {
            $to = $event->message->getTo()
                ? collect($event->message->getTo())->map->toString()->all()
                : [];

            Log::error('Email FAILED', [
                'to'      => $to,
                'subject' => $event->message->getSubject(),
                'error'   => $event->exception->getMessage(),
            ]);
        });
    }
}
