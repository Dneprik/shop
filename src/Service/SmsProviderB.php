<?php
namespace App\Service;

use Psr\Log\LoggerInterface;

class SmsProviderB implements SmsProviderInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function getName(): string
    {
        return 'provider_b';
    }

    public function sendSMS(string $to, string $content): string
    {
        $this->logger->info('[SMS:B] to={to} content={content}', [
            'to' => $to,
            'content' => $content,
        ]);


        return 'SMS send via provider B';
    }
}
