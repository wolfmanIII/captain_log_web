<?php

namespace App\Controller\Admin;

use App\Entity\Insurance;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class InsuranceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Insurance::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        yield Field::new('name');
        yield Field::new('annual_cost');
        yield Field::new('lossRefund')->setLabel('Loss refund (%)')->setHelp('Percentuale di rimborso perdita');

        // 👇 Coverage come lista di stringhe
        yield CollectionField::new('coverage')
            ->setEntryType(TextType::class)
            ->allowAdd()
            ->allowDelete()
            ->setFormTypeOptions([
                'by_reference' => false,   // importantissimo per modificare array JSON
            ])
            ->onlyOnForms();

        // Mostra come array nelle view
        yield ArrayField::new('coverage')
            ->hideOnForm();
    }
    
}
