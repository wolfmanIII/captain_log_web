<?php

namespace App\Form;

use App\Entity\Campaign;
use App\Form\Config\DayYearLimits;
use App\Form\Type\ImperialDateType;
use App\Model\ImperialDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignType extends AbstractType
{
    public function __construct(private readonly DayYearLimits $limits)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Campaign|null $campaign */
        $campaign = $builder->getData();
        $sessionDate = new ImperialDate($campaign?->getSessionYear(), $campaign?->getSessionDay());
        $minYear = $campaign?->getStartingYear() ?? $this->limits->getYearMin();
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 3],
            ])
            ->add('startingYear', IntegerType::class, [
                'label' => 'Starting year',
                'required' => true,
                'attr' => $this->limits->yearAttr(['class' => 'input m-1 w-full']),
            ])
            ->add('sessionDate', ImperialDateType::class, [
                'label' => 'Session date',
                'required' => true,
                'mapped' => false,
                'data' => $sessionDate,
                'min_year' => $minYear,
                'max_year' => $this->limits->getYearMax(),
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            /** @var Campaign $campaign */
            $campaign = $event->getData();
            $form = $event->getForm();

            /** @var ImperialDate|null $session */
            $session = $form->get('sessionDate')->getData();
            if ($session instanceof ImperialDate) {
                $campaign->setSessionDay($session->getDay());
                $campaign->setSessionYear($session->getYear());
            } else {
                $campaign->setSessionDay(null);
                $campaign->setSessionYear(null);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Campaign::class,
        ]);
    }
}
