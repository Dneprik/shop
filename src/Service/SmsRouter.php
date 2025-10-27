<?php
namespace App\Service;

use Symfony\Component\RateLimiter\RateLimiterFactory;
use Psr\Log\LoggerInterface;

// Sms router send sms to clients using 2 providers with limits, which set in rate_limiter


class SmsRouter
{

    public function __construct(
        private iterable $providers,
        private RateLimiterFactory $smsLimiter,
        private LoggerInterface $logger
    ) {}


    public function sendOrderPlaced(string $to, string $orderNumber, string $total): string
    {
        $content = sprintf('Order %s placed. Total: %s', $orderNumber, $total);
        return $this->send($to, $content);
    }

    public function send(string $to, string $content): string
    {
        $lastError = null;

        foreach ($this->providers as $provider) {
            $name = $provider->getName();

            $limiter = $this->smsLimiter->create($name);
            $limit = $limiter->consume();

            if (!$limit->isAccepted()) {
                $this->logger->warning('SMS limit reached for provider {name}', ['name' => $name]);
                continue;
            }

            try {
                $status = $provider->sendSMS($to, $content);
                $this->logger->info('SMS sent via {name}: {status}', ['name' => $name, 'status' => $status]);
                return $status;  // ← Возврат успеха
            } catch (\Throwable $e) {
                $this->logger->error('SMS provider {name} failed: {err}', [
                    'name' => $name,
                    'err' => $e->getMessage(),
                ]);
                $lastError = $e;
            }
        }

        $errorMsg = $lastError ? $lastError->getMessage() : 'Unknown error';
        throw new \RuntimeException('All SMS providers failed: ' . $errorMsg, previous: $lastError);
    }
}
