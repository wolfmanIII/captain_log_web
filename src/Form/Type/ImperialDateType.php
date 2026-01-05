<?php

namespace App\Form\Type;

use App\Model\ImperialDate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImperialDateType extends AbstractType
{
    private const MONTHS = [
        ['label' => 'Holiday (001)', 'value' => 'holiday', 'start' => 1, 'end' => 1],
        ['label' => 'Month 1', 'value' => 1, 'start' => 2, 'end' => 29],
        ['label' => 'Month 2', 'value' => 2, 'start' => 30, 'end' => 57],
        ['label' => 'Month 3', 'value' => 3, 'start' => 58, 'end' => 85],
        ['label' => 'Month 4', 'value' => 4, 'start' => 86, 'end' => 113],
        ['label' => 'Month 5', 'value' => 5, 'start' => 114, 'end' => 141],
        ['label' => 'Month 6', 'value' => 6, 'start' => 142, 'end' => 169],
        ['label' => 'Month 7', 'value' => 7, 'start' => 170, 'end' => 197],
        ['label' => 'Month 8', 'value' => 8, 'start' => 198, 'end' => 225],
        ['label' => 'Month 9', 'value' => 9, 'start' => 226, 'end' => 253],
        ['label' => 'Month 10', 'value' => 10, 'start' => 254, 'end' => 281],
        ['label' => 'Month 11', 'value' => 11, 'start' => 282, 'end' => 309],
        ['label' => 'Month 12', 'value' => 12, 'start' => 310, 'end' => 337],
        ['label' => 'Month 13', 'value' => 13, 'start' => 338, 'end' => 365],
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ImperialDate|null $data */
        $data = $builder->getData();
        $initialDay = $data?->getDay();
        [$defaultMonth, $defaultDayInMonth] = $this->splitDay($initialDay);

        $builder
            ->add('year', IntegerType::class, [
                'required' => true,
                'label' => 'Year',
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'min' => $options['min_year'],
                    'max' => $options['max_year'],
                ],
            ])
            ->add('month', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Month',
                'choices' => $this->getMonthChoices(),
                'data' => $defaultMonth,
                'placeholder' => '-- Month --',
                'attr' => [
                    'class' => 'select select-bordered w-full',
                    'data-imperial-date-target' => 'month',
                    'data-action' => 'change->imperial-date#onMonthChange',
                ],
            ])
            ->add('dayInMonth', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Day',
                'placeholder' => '-- Day --',
                'choices' => $this->dayChoicesForMonth($defaultMonth),
                'data' => $defaultDayInMonth,
                'attr' => [
                    'class' => 'select select-bordered w-full',
                    'data-imperial-date-target' => 'dayInMonth',
                    'data-action' => 'change->imperial-date#onDayChange',
                ],
            ])
            ->add('day', IntegerType::class, [
                'required' => true,
                'label' => 'Day of year',
                'attr' => [
                    'class' => 'input input-bordered w-full',
                    'min' => 1,
                    'max' => 365,
                    'data-imperial-date-target' => 'day',
                    'data-initial-day' => $initialDay ?? '',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!is_array($data)) {
                return;
            }

            $month = $data['month'] ?? null;
            $dayInMonth = $data['dayInMonth'] ?? null;

            if ($month !== null && $dayInMonth !== null && $dayInMonth !== '') {
                $computed = $this->combineDay($month, (int) $dayInMonth);
                if ($computed !== null) {
                    $data['day'] = $computed;
                    $event->setData($data);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImperialDate::class,
            'min_year' => 1105,
            'max_year' => 9999,
            'attr' => [
                'data-controller' => 'imperial-date',
            ],
        ]);
    }

    private function getMonthChoices(): array
    {
        $choices = [];
        foreach (self::MONTHS as $month) {
            $choices[$month['label']] = (string) $month['value'];
        }

        return $choices;
    }

    private function dayChoicesForMonth(string|int|null $month): array
    {
        $monthInfo = $this->findMonth($month);
        if ($monthInfo === null) {
            return [];
        }

        $choices = [];
        for ($day = $monthInfo['start']; $day <= $monthInfo['end']; $day++) {
            $dayInMonth = $day - $monthInfo['start'] + 1;
            $choices[sprintf('%03d', $day)] = $dayInMonth;
        }

        return $choices;
    }

    private function splitDay(?int $day): array
    {
        if ($day === null) {
            return [null, null];
        }

        foreach (self::MONTHS as $month) {
            if ($day >= $month['start'] && $day <= $month['end']) {
                $dayInMonth = $day - $month['start'] + 1;
                return [(string) $month['value'], $dayInMonth];
            }
        }

        return [null, null];
    }

    private function combineDay(string|int $monthValue, int $dayInMonth): ?int
    {
        $month = $this->findMonth($monthValue);
        if ($month === null) {
            return null;
        }

        $start = $month['start'];
        $end = $month['end'];
        $absoluteDay = $start + ($dayInMonth - 1);

        if ($absoluteDay < $start || $absoluteDay > $end) {
            return null;
        }

        return $absoluteDay;
    }

    private function findMonth(string|int|null $value): ?array
    {
        foreach (self::MONTHS as $month) {
            if ((string) $month['value'] === (string) $value) {
                return $month;
            }
        }

        return null;
    }
}
