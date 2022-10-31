<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class SmsExceptionListener
{
    private string $recipient;
    private NotifierInterface $notifier;

    public function __construct(
        string $recipient,
        NotifierInterface $notifier
    ) {
        $this->recipient = $recipient;
        $this->notifier = $notifier;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();

        $template = <<<EOF
Something went wrong with the application.

Here are the specifics:
- Error Message: %s.
- Occurred in file: "%s" on Line: %d
EOF;

        $content = sprintf(
            $template,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        );
        $notification = new Notification($content, ['sms']);
        //$notification->content($content);
        $notification->importance(Notification::IMPORTANCE_URGENT);

        $recipient = new Recipient('', $this->recipient);

        $this->notifier->send($notification, $recipient);
    }
}