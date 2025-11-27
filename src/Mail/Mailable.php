<?php

namespace NeoPhp\Mail;

use NeoPhp\Queue\ShouldQueue;

/**
 * Mailable
 * 
 * Base class for email messages
 */
abstract class Mailable
{
    /**
     * The recipients of the message
     */
    public array $to = [];

    /**
     * The CC recipients of the message
     */
    public array $cc = [];

    /**
     * The BCC recipients of the message
     */
    public array $bcc = [];

    /**
     * The subject of the message
     */
    public ?string $subject = null;

    /**
     * The view to use for the message
     */
    public ?string $view = null;

    /**
     * The plain text view to use for the message
     */
    public ?string $textView = null;

    /**
     * The view data
     */
    public array $viewData = [];

    /**
     * The attachments for the message
     */
    public array $attachments = [];

    /**
     * The raw attachments for the message
     */
    public array $rawAttachments = [];

    /**
     * The mailer that should send the message
     */
    public ?string $mailer = null;

    /**
     * Build the message
     */
    abstract public function build(): self;

    /**
     * Set the recipients
     */
    public function to($address, string $name = null): self
    {
        if (is_array($address)) {
            $this->to = array_merge($this->to, $address);
        } else {
            $this->to[] = ['address' => $address, 'name' => $name];
        }

        return $this;
    }

    /**
     * Set the CC recipients
     */
    public function cc($address, string $name = null): self
    {
        if (is_array($address)) {
            $this->cc = array_merge($this->cc, $address);
        } else {
            $this->cc[] = ['address' => $address, 'name' => $name];
        }

        return $this;
    }

    /**
     * Set the BCC recipients
     */
    public function bcc($address, string $name = null): self
    {
        if (is_array($address)) {
            $this->bcc = array_merge($this->bcc, $address);
        } else {
            $this->bcc[] = ['address' => $address, 'name' => $name];
        }

        return $this;
    }

    /**
     * Set the subject
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the view and view data
     */
    public function view(string $view, array $data = []): self
    {
        $this->view = $view;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }

    /**
     * Set the plain text view
     */
    public function text(string $textView, array $data = []): self
    {
        $this->textView = $textView;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }

    /**
     * Set the view data
     */
    public function with($key, $value = null): self
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }

    /**
     * Attach a file
     */
    public function attach(string $file, array $options = []): self
    {
        $this->attachments[] = compact('file', 'options');
        return $this;
    }

    /**
     * Attach in-memory data
     */
    public function attachData(string $data, string $name, array $options = []): self
    {
        $this->rawAttachments[] = compact('data', 'name', 'options');
        return $this;
    }

    /**
     * Set the mailer
     */
    public function mailer(string $mailer): self
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * Send the mailable
     */
    public function send(): void
    {
        $this->build();

        $mailer = app(Mailer::class);

        // Set recipients
        foreach ($this->to as $recipient) {
            $mailer->to($recipient['address'], $recipient['name'] ?? '');
        }

        // Set subject
        if ($this->subject) {
            $mailer->subject($this->subject);
        }

        // Render view
        if ($this->view) {
            $content = $this->renderView();
            $mailer->html($content);
        }

        // Send
        $mailer->send();
    }

    /**
     * Queue the mailable
     */
    public function queue(): void
    {
        if ($this instanceof ShouldQueue) {
            // Dispatch to queue
            $job = new SendMailableJob($this);
            $job::dispatch($job);
        } else {
            $this->send();
        }
    }

    /**
     * Queue the mailable with delay
     */
    public function later(int $delay): void
    {
        if ($this instanceof ShouldQueue) {
            $job = new SendMailableJob($this);
            $job::dispatch($job)->delay($delay);
        } else {
            $this->send();
        }
    }

    /**
     * Render the view
     */
    protected function renderView(): string
    {
        if (!$this->view) {
            return '';
        }

        $viewEngine = app('view');
        return $viewEngine->render($this->view, $this->viewData);
    }

    /**
     * Create markdown content
     */
    public function markdown(string $view, array $data = []): self
    {
        $this->view = $view;
        $this->viewData = array_merge($this->viewData, $data);
        // Could add markdown parsing here
        return $this;
    }
}
