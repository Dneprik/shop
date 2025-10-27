<?php
namespace App\Service;

interface SmsProviderInterface
{

    public function sendSMS(string $to, string $content): string;

    public function getName(): string;
}
