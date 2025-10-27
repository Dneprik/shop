<?php
namespace App\Controller;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\Form\FormError;
use App\Form\OrderPurchaseType;
use App\Repository\OrderRepository;
use App\Service\OrderingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orders')]
class   OrderWebController extends AbstractController
{

    #[Route('/new', name: 'order_new', methods: ['GET','POST'])]
    public function new(Request $request, OrderingService $ordering,): Response
    {
        $form = $this->createForm(OrderPurchaseType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $phone        = (string)$form->get('phone')->getData();
            $articlesData = $form->get('articles')->getData();
            $articles = $articlesData instanceof Collection
                ? $articlesData->toArray()
                : (is_array($articlesData) ? $articlesData : []);
            $subscription = $form->get('subscription')->getData();


             try {
                 $result = $ordering->placeOrder($phone, $articles, $subscription);
                 $order = $result['order'];
                 $smsStatus = $result['smsStatus'];


                $this->addFlash('success', sprintf('Order %s created (total %s). Status SMS: %s', $order->getOrderNumber(), $order->getTotalPrice(), $smsStatus));
                return $this->redirectToRoute('order_confirm', ['number' => $order->getOrderNumber()]);
            } catch (\InvalidArgumentException|\RuntimeException $e) {
                 $this->addFlash('error', $e->getMessage());
                 return $this->redirectToRoute('order_new');
            }
        }


        return $this->render('order/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/confirm/{number}', name: 'order_confirm', methods: ['GET'])]
    public function confirm(string $number, OrderRepository $orders): Response
    {
        $order = $orders->findOneBy(['orderNumber' => $number]);
        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('order/confirm.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/by-phone', name: 'order_by_phone', methods: ['GET'])]
    public function byPhone(Request $request, OrderRepository $orders): Response
    {
        $phone = (string)$request->query->get('phone', '');
        $list = $phone ? $orders->findByCustomerPhone($phone) : [];

        return $this->render('order/by_phone.html.twig', [
            'phone' => $phone,
            'orders' => $list,
        ]);
    }

    #[Route('/show/{number}', name: 'show_order', methods: ['GET'])]
    public function showOrder(string $number, OrderRepository $orders): Response
    {
        $order = $orders->findOneBy(['orderNumber' => $number]);
        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/show-orders', name: 'show_orders', methods: ['GET'])]
    public function showAllOrders(OrderRepository $orders): Response
    {
        $orderslist = $orders->findAll();

        return $this->render('order/show_all.html.twig', [
            'orders' => $orderslist,
        ]);
    }

    #[Route('/{number}/delete', name: 'order_delete', methods: ['POST'])]
    public function deleteOrder(
        Request $request,
        string $number,
        OrderRepository $orders,
        EntityManagerInterface $em
    ): Response {
        $order = $orders->findOneBy(['orderNumber' => $number]);
        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        // Проверка CSRF
        if (!$this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('show_orders');
        }

        // ⚙️ Очистка связанных записей
        foreach ($order->getItems() as $item) {
            if ($item->getArticle()) {
                $customerArticle = $em->getRepository(\App\Entity\CustomerArticle::class)
                    ->findOneBy([
                        'customer' => $order->getCustomer(),
                        'article' => $item->getArticle(),
                    ]);
                if ($customerArticle) {
                    $em->remove($customerArticle);
                }
            }

            if ($item->getSubscription()) {
                $customerSub = $em->getRepository(\App\Entity\CustomerSubscription::class)
                    ->findOneBy([
                        'customer' => $order->getCustomer(),
                        'subscription' => $item->getSubscription(),
                    ]);
                if ($customerSub) {
                    $em->remove($customerSub);
                }
            }


            $em->remove($item);
        }


        $em->remove($order);

        try {
            $em->flush();
            $this->addFlash('success', sprintf('Order %s and related records deleted.', $number));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Failed to delete order: ' . $e->getMessage());
        }

        return $this->redirectToRoute('show_orders');
    }


}
