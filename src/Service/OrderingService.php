<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Customer;
use App\Entity\Article;
use App\Entity\SubscriptionPackage;
use App\Entity\CustomerArticle;
use App\Entity\CustomerSubscription;

use App\Repository\CustomerRepository;
use App\Repository\CustomerArticleRepository;
use App\Repository\CustomerSubscriptionRepository;

use Doctrine\ORM\EntityManagerInterface;
use App\Service\SmsRouter;

class OrderingService
{
    public function __construct(
        private EntityManagerInterface         $em,
        private CustomerRepository             $customers,
        private CustomerArticleRepository      $customerArticles,
        private CustomerSubscriptionRepository $customerSubscriptions,
        private SmsRouter                      $sms,
    ) {}

    // Create order based on phone, list of articles, and subscription
    public function placeOrder(string $phone, array $articles, ?SubscriptionPackage $subscription): array
    {
        $phone = trim($phone);
        if ($phone === '') {
            throw new \InvalidArgumentException('Phone is required.');
        }
        if (empty($articles) && $subscription === null) {
            throw new \InvalidArgumentException('Select at least one article or a subscription.');
        }

        $customer = $this->customers->findOneBy(['phone' => $phone]);
        if (!$customer) {
            $customer = new Customer($phone);
            $this->em->persist($customer);
            $this->em->flush();
        }

        $exist_articles = [];
        foreach ($articles as $a) {
            if (!$a instanceof Article) continue;

            $already = $this->customerArticles->findOneBy([
                'customer' => $customer,
                'article' => $a
            ]);

            if ($already) {
                $exist_articles[] = $a->getName();
            }
        }

        if (!empty($exist_articles)) {
            $articleList = implode(', ', $exist_articles);
            throw new \RuntimeException(sprintf(
                'Articles was already purchased by this customer: %s', $articleList
            ));
        }



        if ($subscription) {
            $active = $this->customerSubscriptions->findOneBy([
                'customer' => $customer,
                'status' => 'ACTIVE'
            ]);
            if ($active) {
                throw new \RuntimeException(sprintf('Customer already has an active subscription: %s', $subscription->getName()));
            }
        }

        // Create order
        $orderNumber = $this->generateOrderNumber();
        $order = new Order($customer, $orderNumber);
        $order->setStatus('PAID');

        $total = 0.0;


        foreach ($articles as $a) {
            if (!$a instanceof Article) continue;

            $item = new OrderItem($order, $a->getPrice());
            $item->setArticle($a);
            $this->em->persist($item);

            $total += (float)$a->getPrice();
        }


        if ($subscription) {
            $item = new OrderItem($order, $subscription->getPrice());
            $item->setSubscription($subscription);
            $this->em->persist($item);

            $total += (float)$subscription->getPrice();
        }

        $order->setTotalPrice(number_format($total, 2, '.', ''));


        foreach ($articles as $a) {
            if (!$a instanceof Article) continue;

            $ca = new CustomerArticle($customer, $a);
            $this->em->persist($ca);
        }

        if ($subscription) {
            $cs = new CustomerSubscription($customer, $subscription);
            $cs->setStatus('ACTIVE');
            $this->em->persist($cs);
        }


        $this->em->persist($order);
        $this->em->flush();

        // Send SMS
        try {
            $smsStatus = $this->sms->sendOrderPlaced($phone, $order->getOrderNumber(), $order->getTotalPrice());

            if (strpos($smsStatus, 'error') === 0) {
                $this->logger->warning('SMS failed during order: ' . $smsStatus);
            }
        } catch (\RuntimeException $e) {
            $this->logger->error('SMS all failed: ' . $e->getMessage());
        }

        return [
            'order' => $order,
            'smsStatus' => $smsStatus,
        ];
    }


    private function generateOrderNumber(): string
    {
        return 'ORDER-' . (new \DateTimeImmutable())->format('Ymd-His') . '-' .
            strtoupper(bin2hex(random_bytes(2)));
    }
}
