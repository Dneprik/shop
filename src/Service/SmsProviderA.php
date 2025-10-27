<?php
namespace App\Service;

use Psr\Log\LoggerInterface;

class SmsProviderA implements SmsProviderInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function getName(): string
    {
        return 'provider_a';
    }

    public function sendSMS(string $to, string $content): string
    {
        $this->logger->info('[SMS:A] to={to} content={content}', [
            'to' => $to,
            'content' => $content,
        ]);



        return 'SMS send via provider A';
    }
}
