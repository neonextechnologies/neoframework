<?php

namespace NeoPhp\Auth\EmailVerification;

use NeoPhp\Mail\Mailer;

/**
 * Verifies Emails Trait
 * 
 * Add this trait to User model to implement email verification
 */
trait VerifiesEmails
{
    /**
     * Determine if the user has verified their email address
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the user's email as verified
     */
    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Send the email verification notification
     */
    public function sendEmailVerificationNotification(): void
    {
        $tokens = app(EmailVerificationToken::class);
        $token = $tokens->create($this->id, $this->email);

        $verificationUrl = $this->getVerificationUrl($token);

        $mailer = app(Mailer::class);
        $mailer->to($this->email)
            ->subject('Verify Email Address')
            ->html($this->getVerificationEmailBody($verificationUrl))
            ->send();
    }

    /**
     * Get the email address that should be verified
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    /**
     * Get verification URL
     */
    protected function getVerificationUrl(string $token): string
    {
        $baseUrl = config('app.url', 'http://localhost');
        return "{$baseUrl}/email/verify?id={$this->id}&token={$token}";
    }

    /**
     * Get verification email body
     */
    protected function getVerificationEmailBody(string $url): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background: #28a745; color: #fff; text-decoration: none; border-radius: 4px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Email Address</h2>
        <p>Please click the button below to verify your email address.</p>
        <p>
            <a href="{$url}" class="button">Verify Email Address</a>
        </p>
        <p>If you did not create an account, no further action is required.</p>
        <div class="footer">
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p>{$url}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
