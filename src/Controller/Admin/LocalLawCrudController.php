<?php

namespace App\Controller\Admin;

use App\Entity\LocalLaw;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class LocalLawCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LocalLaw::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('code'),
            TextField::new('shortDescription'),
            TextareaField::new('description')->renderAsHtml(false),
            TextareaField::new('disclaimer')->renderAsHtml(false)->setRequired(false),
        ];
    }
}
