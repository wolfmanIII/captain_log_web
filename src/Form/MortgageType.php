<?php

namespace App\Form;

use App\Entity\Insurance;
use App\Entity\InterestRate;
use App\Entity\Mortgage;
use App\Entity\Ship;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MortgageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('startDay')
            ->add('startYear')
            ->add('shipShares')
            ->add('advancePayment')
            ->add('discount')
            ->add('ship', EntityType::class, [
                'class' => Ship::class,
                'choice_label' => 'id',
            ])
            ->add('interestRate', EntityType::class, [
                'class' => InterestRate::class,
                'choice_label' => 'id',
            ])
            ->add('insurance', EntityType::class, [
                'class' => Insurance::class,
                'choice_label' => 'name',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mortgage::class,
        ]);
    }
}
