<?php

namespace App\Form\Type;

use App\Entity\IncomePassengersDetails;
use App\Form\Config\DayYearLimits;
use App\Form\Type\ImperialDateType;
use App\Model\ImperialDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncomePassengersDetailsType extends AbstractType
{
    public function __construct(private readonly DayYearLimits $limits)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $campaignStartYear = $options['campaign_start_year'] ?? null;
        $minYear = $campaignStartYear ?? $this->limits->getYearMin();
        /** @var IncomePassengersDetails|null $data */
        $data = $builder->getData();
        $departureDate = new ImperialDate($data?->getDepartureYear(), $data?->getDepartureDay());
        $arrivalDate = new ImperialDate($data?->getArrivalYear(), $data?->getArrivalDay());
        $builder
            ->add('origin', TextType::class, [
                'required' => false,
                'label' => 'Origin',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('destination', TextType::class, [
                'required' => false,
                'label' => 'Destination',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('departureDate', ImperialDateType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Departure date',
                'data' => $departureDate,
                'min_year' => $minYear,
                'max_year' => $this->limits->getYearMax(),
            ])
            ->add('arrivalDate', ImperialDateType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Arrival date',
                'data' => $arrivalDate,
                'min_year' => $minYear,
                'max_year' => $this->limits->getYearMax(),
            ])
            ->add('classOrBerth', TextType::class, [
                'required' => false,
                'label' => 'Class / Berth',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('qty', NumberType::class, [
                'required' => false,
                'label' => 'Qty',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('passengerNames', TextareaType::class, [
                'required' => false,
                'label' => 'Passenger names',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ])
            ->add('passengerContact', TextType::class, [
                'required' => false,
                'label' => 'Passenger contact',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('baggageAllowance', TextType::class, [
                'required' => false,
                'label' => 'Baggage allowance',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('extraBaggage', TextType::class, [
                'required' => false,
                'label' => 'Extra baggage',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('paymentTerms', TextareaType::class, [
                'required' => false,
                'label' => 'Payment terms',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ])
            ->add('refundChangePolicy', TextareaType::class, [
                'required' => false,
                'label' => 'Refund/Change policy',
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 2],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            /** @var IncomePassengersDetails $details */
            $details = $event->getData();
            $form = $event->getForm();

            /** @var ImperialDate|null $dep */
            $dep = $form->get('departureDate')->getData();
            if ($dep instanceof ImperialDate) {
                $details->setDepartureDay($dep->getDay());
                $details->setDepartureYear($dep->getYear());
            }

            /** @var ImperialDate|null $arr */
            $arr = $form->get('arrivalDate')->getData();
            if ($arr instanceof ImperialDate) {
                $details->setArrivalDay($arr->getDay());
                $details->setArrivalYear($arr->getYear());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IncomePassengersDetails::class,
            'campaign_start_year' => null,
        ]);
    }
}
