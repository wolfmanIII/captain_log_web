<?php

namespace App\Form;

use App\Entity\AnnualBudget;
use App\Entity\Ship;
use App\Form\Config\DayYearLimits;
use App\Form\Type\ImperialDateType;
use App\Model\ImperialDate;
use App\Repository\ShipRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnualBudgetType extends AbstractType
{
    public function __construct(private readonly DayYearLimits $limits)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        /** @var AnnualBudget $budget */
        $budget = $builder->getData();
        $campaignStartYear = $budget?->getShip()?->getCampaign()?->getStartingYear();
        $minYear = max($this->limits->getYearMin(), $campaignStartYear ?? $this->limits->getYearMin());
        $startDate = new ImperialDate($budget?->getStartYear(), $budget?->getStartDay());
        $endDate = new ImperialDate($budget?->getEndYear(), $budget?->getEndDay());

        $builder
            ->add('startDate', ImperialDateType::class, [
                'mapped' => false,
                'label' => 'Start date',
                'required' => true,
                'data' => $startDate,
                'min_year' => $minYear,
                'max_year' => $this->limits->getYearMax(),
            ])
            ->add('endDate', ImperialDateType::class, [
                'mapped' => false,
                'label' => 'End date',
                'required' => true,
                'data' => $endDate,
                'min_year' => $minYear,
                'max_year' => $this->limits->getYearMax(),
            ])
            ->add('ship', EntityType::class, [
                'class' => Ship::class,
                'placeholder' => '-- Select a Ship --',
                'choice_label' => fn (Ship $ship) => sprintf('%s (%s)', $ship->getName(), $ship->getClass()),
                'choice_attr' => function (Ship $ship): array {
                    $start = $ship->getCampaign()?->getStartingYear();
                    return ['data-start-year' => $start ?? ''];
                },
                'query_builder' => function (ShipRepository $repo) use ($user) {
                    $qb = $repo->createQueryBuilder('s')->orderBy('s.name', 'ASC');
                    if ($user) {
                        $qb->andWhere('s.user = :user')->setParameter('user', $user);
                    }
                    $qb->andWhere('s.campaign IS NOT NULL');
                    return $qb;
                },
                'attr' => [
                    'class' => 'select m-1 w-full',
                    'data-controller' => 'year-limit',
                    'data-year-limit-default-value' => $this->limits->getYearMin(),
                    'data-action' => 'change->year-limit#onShipChange',
                ],
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 3],
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            /** @var AnnualBudget $budget */
            $budget = $event->getData();
            $form = $event->getForm();

            /** @var ImperialDate|null $start */
            $start = $form->get('startDate')->getData();
            if ($start instanceof ImperialDate) {
                $budget->setStartDay($start->getDay());
                $budget->setStartYear($start->getYear());
            }

            /** @var ImperialDate|null $end */
            $end = $form->get('endDate')->getData();
            if ($end instanceof ImperialDate) {
                $budget->setEndDay($end->getDay());
                $budget->setEndYear($end->getYear());
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            /** @var ImperialDate|null $start */
            $start = $form->get('startDate')->getData();
            /** @var ImperialDate|null $end */
            $end = $form->get('endDate')->getData();

            if (!$start instanceof ImperialDate || !$end instanceof ImperialDate) {
                return;
            }

            $startYear = $start->getYear();
            $endYear = $end->getYear();
            $startDay = $start->getDay();
            $endDay = $end->getDay();

            if ($startYear === null || $endYear === null || $startDay === null || $endDay === null) {
                return;
            }

            $invalid = $endYear < $startYear || ($endYear === $startYear && $endDay < $startDay);
            if ($invalid) {
                $form->get('endDate')->addError(new FormError('End date must be after or equal to start date.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnnualBudget::class,
            'user' => null,
        ]);
    }
}
