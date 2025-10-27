<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\SubscriptionPackage;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class OrderPurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phone', TextType::class, [
                'label' => 'Customer phone',
                'constraints' => [
                    new Assert\NotBlank(message: 'Phone is required'),
                    new Assert\Length(max: 32),
                ],
            ])
            ->add('articles', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true, // чекбоксы
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->andWhere('a.isDeleted = 0')
                        ->orderBy('a.name', 'ASC');
                },
            ])
            ->add('subscription', EntityType::class, [
                'class' => SubscriptionPackage::class,
                'choice_label' => 'name',
                'placeholder' => 'No subscription',
                'required' => false,
                'expanded' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->andWhere('s.isDeleted = 0')
                        ->orderBy('s.name', 'ASC');
                },
            ]);
    }
}
