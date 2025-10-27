<?php

namespace App\Controller;

use App\Entity\SubscriptionPackage;
use App\Form\SubscriptionPackageType;
use App\Repository\CustomerSubscriptionRepository;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subscription/package')]
final class SubscriptionPackageController extends AbstractController
{
    #[Route(name: 'app_subscription_package_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $subscriptionPackages = $entityManager
            ->getRepository(SubscriptionPackage::class)
            ->findAll();

        return $this->render('subscription_package/index.html.twig', [
            'subscription_packages' => $subscriptionPackages,
        ]);
    }

    #[Route('/new', name: 'app_subscription_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subscriptionPackage = new SubscriptionPackage();
        $form = $this->createForm(SubscriptionPackageType::class, $subscriptionPackage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subscriptionPackage);
            $entityManager->flush();

            return $this->redirectToRoute('app_subscription_package_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('subscription_package/new.html.twig', [
            'subscription_package' => $subscriptionPackage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_subscription_package_show', methods: ['GET'])]
    public function show(SubscriptionPackage $subscriptionPackage): Response
    {
        return $this->render('subscription_package/show.html.twig', [
            'subscription_package' => $subscriptionPackage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_subscription_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SubscriptionPackage $subscriptionPackage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubscriptionPackageType::class, $subscriptionPackage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_subscription_package_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('subscription_package/edit.html.twig', [
            'subscription_package' => $subscriptionPackage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_subscription_package_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        SubscriptionPackage $subscriptionPackage,
        EntityManagerInterface $em,
        OrderItemRepository $orderItems,
        CustomerSubscriptionRepository $customerSubscriptions
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$subscriptionPackage->getId(), $request->request->get('_token')))
        {
         return $this->redirectToRoute('app_subscription_package_index');
        }


        $hasOrderItems = (bool) $orderItems->createQueryBuilder('i')
            ->select('1')->andWhere('i.subscription = :s')->SetParameter('s', $subscriptionPackage)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        $hasCustomerSubscriptions = (bool) $customerSubscriptions->createQueryBuilder('ca')
            ->select('1')->andWhere('ca.subscription = :s')->SetParameter('s', $subscriptionPackage)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();

        if ($hasOrderItems || $hasCustomerSubscriptions) {
            $subscriptionPackage->setIsDeleted(true);
            $em->flush();
            $this->addFlash('success', 'Subscription archived (it was purchased)');
            return  $this->redirectToRoute('app_subscription_package_index');

        }
        $em->remove($subscriptionPackage);
        try {
            $em->flush();
            $this->addFlash('success', 'Subscription deleted.');
        }
        catch (ForeignKeyConstraintViolationException $e) {
            $this->addFlash('error', 'Subscription has purchases and cannot be deleted. It was not removed.');
        }
        return $this->redirectToRoute('app_subscription_package_index');


    }
}
