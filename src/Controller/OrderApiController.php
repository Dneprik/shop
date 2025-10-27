<?php
namespace App\Controller;

use App\Repository\{ArticleRepository, SubscriptionPackageRepository, OrderRepository};
use App\Service\OrderingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/orders', name: 'api_orders_')]
class OrderApiController extends AbstractController
{
    #[Route('', name: 'api_orders_create', methods: ['POST'])]
    public function create(
        Request $request,
        ArticleRepository $articles,
        SubscriptionPackageRepository $subs,
        OrderingService $ordering
    ): JsonResponse {
        $data = $request->toArray();

        $phone = (string)($data['phone'] ?? '');
        $articleIds = (array)($data['articleIds'] ?? []);
        $subscriptionId = $data['subscriptionId'] ?? null;

        $selectedArticles = $articleIds ? $articles->findBy(['id' => $articleIds]) : [];
        $subscription = $subscriptionId ? $subs->find($subscriptionId) : null;

        try {
            $result = $ordering->placeOrder($phone, $selectedArticles, $subscription);
            $order = $result['order'];
            $smsStatus = $result['smsStatus'];
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json([
            'orderNumber' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'total' => $order->getTotalPrice(),
            'customerPhone' => $phone,
            'articles' => array_map(fn($a) => $a->getName(), $selectedArticles),
            'subscription' => $subscription ? $subscription->getName() : null,
            'createdAt' => $order->getCreatedAt()->format(  'Y-m-d H:i:s'),
        ], 201);
    }

    #[Route('', name: 'api_orders_by_phone', methods: ['GET'])]
    public function byPhone(Request $request, OrderRepository $orders): JsonResponse
    {
        $phone = (string)$request->query->get('phone', '');
        if ($phone === '') {
            return $this->json(['error' => 'phone is required'], 400);
        }

        $list = [];
        foreach ($orders->findByCustomerPhone($phone) as $o) {
            $articles = [];
            $subscriptions = null;

            foreach ($o->getItems() as $item) {
                if ($item->getArticle()) {
                    $articles[] = $item->getArticle()->getName();
                }
                elseif ($item->getSubscription()) {
                    $subscriptions[] = $item->getSubscription()->getName();
                }
            }

            $list[] = [
                'id' => $o->getId(),
                'orderNumber' => $o->getOrderNumber(),
                'status' => $o->getStatus(),
                'total' => $o->getTotalPrice(),
                'customerPhone' => $phone,
                'articles' => $articles,
                'subscription' => $subscriptions,
                'createdAt' => $o->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        return $this->json($list);
    }

    #[Route('', name: 'api_orders_by_phone', methods: ['GET'])]
    public function All(Request $request, OrderRepository $orders): JsonResponse
    {

        $list = [];
        foreach ($orders->findAll() as $o) {
            $articles = [];
            $subscriptions = null;

            foreach ($o->getItems() as $item) {
                if ($item->getArticle()) {
                    $articles[] = $item->getArticle()->getName();
                }
                elseif ($item->getSubscription()) {
                    $subscriptions[] = $item->getSubscription()->getName();
                }
            }

            $list[] = [
                'id' => $o->getId(),
                'orderNumber' => $o->getOrderNumber(),
                'status' => $o->getStatus(),
                'total' => $o->getTotalPrice(),
                'customerPhone' => $o->getCustomer()->getPhone(),
                'articles' => $articles,
                'subscription' => $subscriptions,
                'createdAt' => $o->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }
        return $this->json($list);
    }
    #[Route('/{number}', name: 'api_order_delete', methods: ['DELETE'])]
    public function delete(string $number, OrderRepository $orders, EntityManagerInterface $em): JsonResponse
    {
        $order = $orders->findOneBy(['id' => $number]);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        // Очистка связанных записей (как в web)
        $customerArticlesRepo = $em->getRepository(\App\Entity\CustomerArticle::class);
        $customerSubsRepo = $em->getRepository(\App\Entity\CustomerSubscription::class);

        foreach ($order->getItems() as $item) {
            if ($item->getArticle()) {

                $ownership = $customerArticlesRepo->findOneBy([
                    'customer' => $order->getCustomer(),
                    'article' => $item->getArticle(),
                ]);
                if ($ownership) {
                    $em->remove($ownership);
                }
            }

            if ($item->getSubscription()) {

                $sub = $customerSubsRepo->findOneBy([
                    'customer' => $order->getCustomer(),
                    'subscription' => $item->getSubscription(),
                ]);
                if ($sub) {
                    $em->remove($sub);
                }
            }


            $em->remove($item);
        }


        $em->remove($order);

        try {
            $em->flush();
            return $this->json([
                'success' => true,
                'message' => 'Order ' . $number . ' and related records deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to delete order: ' . $e->getMessage()
            ], 500);
        }
    }

}
