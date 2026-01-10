<?php

namespace App\Form;

use App\Dto\ShipDetailsData;
use App\Entity\Cost;
use App\Entity\Ship;
use App\Entity\ShipAmendment;
use App\Form\Config\DayYearLimits;
use App\Form\Type\ImperialDateType;
use App\Model\ImperialDate;
use App\Repository\CostRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShipAmendmentType extends AbstractType
{
    public function __construct(private readonly DayYearLimits $limits)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ShipAmendment $amendment */
        $amendment = $options['data'];
        /** @var Ship $ship */
        $ship = $options['ship'];
        $user = $options['user'];

        $minYear = $ship->getCampaign()?->getStartingYear() ?? $this->limits->getYearMin();
        $effectiveDate = new ImperialDate($amendment->getEffectiveYear(), $amendment->getEffectiveDay());
        $detailsData = ShipDetailsData::fromArray($amendment->getPatchDetails() ?? []);

        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'input m-1 w-full'],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'textarea m-1 w-full', 'rows' => 6],
            ])
            ->add('effectiveDate', ImperialDateType::class, [
                'mapped' => false,
                'label' => 'Effective date',
                'data' => $effectiveDate,
                'min_year' => $minYear,
                'max_year' => $this->limits->getYearMax(),
            ])
            ->add('cost', EntityType::class, [
                'class' => Cost::class,
                'required' => false,
                'placeholder' => '-- Optional cost reference --',
                'choice_label' => fn (Cost $cost) => sprintf('%s — %s', $cost->getTitle(), $cost->getAmount()),
                'query_builder' => function (CostRepository $repo) use ($ship, $user) {
                    $qb = $repo->createQueryBuilder('c')
                        ->leftJoin('c.costCategory', 'cc')
                        ->andWhere('c.ship = :ship')
                        ->andWhere('cc.code IN (:codes)')
                        ->setParameter('ship', $ship)
                        ->setParameter('codes', ['SHIP_GEAR', 'SHIP_SOFTWARE'])
                        ->orderBy('c.paymentYear', 'DESC')
                        ->addOrderBy('c.paymentDay', 'DESC');

                    if ($user) {
                        $qb->andWhere('c.user = :user')->setParameter('user', $user);
                    }

                    return $qb;
                },
                'attr' => ['class' => 'select m-1 w-full'],
            ])
            ->add('patchDetails', ShipDetailsType::class, [
                'mapped' => false,
                'data' => $detailsData,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            /** @var ShipAmendment $amendment */
            $amendment = $event->getData();
            $form = $event->getForm();

            /** @var ImperialDate|null $effective */
            $effective = $form->get('effectiveDate')->getData();
            if ($effective instanceof ImperialDate) {
                $amendment->setEffectiveDay($effective->getDay());
                $amendment->setEffectiveYear($effective->getYear());
            }

            /** @var ShipDetailsData $details */
            $details = $form->get('patchDetails')->getData();
            if ($details instanceof ShipDetailsData) {
                $amendment->setPatchDetails($details->toArray());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShipAmendment::class,
            'ship' => null,
            'user' => null,
        ]);
    }
}
