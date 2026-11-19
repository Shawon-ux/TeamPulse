<?php
namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content,Envelope};

class InvitationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    public function __construct(public Invitation $invitation, public string $url) {}
    public function envelope(): Envelope { return new Envelope(subject: "You're invited to join {$this->invitation->team->name} on TeamPulse"); }
    public function content(): Content { return new Content(htmlString:
        "<p>{$this->invitation->inviter->name} invited you to <b>{$this->invitation->team->name}</b> as {$this->invitation->role}.</p>".
        "<p><a href=\"{$this->url}\">Accept invitation</a> (expires {$this->invitation->expires_at->toDayDateTimeString()})</p>"); }
}
