<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkflowNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $title;
    public string $intro;
    public array $details;

    public function __construct(string $subjectLine, string $title, string $intro, array $details = [])
    {
        $this->subjectLine = $subjectLine;
        $this->title = $title;
        $this->intro = $intro;
        $this->details = $details;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.workflow-notification');
    }
}