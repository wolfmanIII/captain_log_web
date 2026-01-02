<?php

namespace App\Form\Type;

use App\Entity\IncomePrizeDetails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncomePrizeDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('caseRef', TextType::class, [
                'required' => false,
                'label' => 'Case ref',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('jurisdiction', TextType::class, [
                'required' => false,
                'label' => 'Jurisdiction',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('legalBasis', TextareaType::class, [
                'required' => false,
                'label' => 'Legal basis',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ])
            ->add('prizeDescription', TextareaType::class, [
                'required' => false,
                'label' => 'Prize description',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ])
            ->add('estimatedValue', NumberType::class, [
                'required' => false,
                'label' => 'Estimated value (Cr)',
                'scale' => 2,
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('disposition', TextType::class, [
                'required' => false,
                'label' => 'Disposition',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('paymentTerms', TextareaType::class, [
                'required' => false,
                'label' => 'Payment terms',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ])
            ->add('shareSplit', TextareaType::class, [
                'required' => false,
                'label' => 'Share split',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ])
            ->add('awardTrigger', TextareaType::class, [
                'required' => false,
                'label' => 'Award trigger',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IncomePrizeDetails::class,
        ]);
    }
}
