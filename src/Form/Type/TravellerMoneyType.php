<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class TravellerMoneyType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'currency' => false,
            'grouping' => false,
            'scale' => 2,
            'html5' => false,
            'input' => 'string',
        ]);

    }

    public function getParent(): string
    {
        return MoneyType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'traveller_money';
    }
}
