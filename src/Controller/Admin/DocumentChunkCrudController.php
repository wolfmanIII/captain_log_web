<?php

namespace App\Controller\Admin;

use App\Entity\DocumentChunk;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class DocumentChunkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocumentChunk::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Chunk documento')
            ->setEntityLabelInPlural('Chunk documenti')
            ->setDefaultSort(['file.path' => 'ASC', 'chunkIndex' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        // Anche qui: i chunk li gestisce il command
        // DELETE c'è già, niente doppioni
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id')->hideOnForm();

        $file = AssociationField::new('file', 'File')
            ->setHelp('Documento a cui appartiene questo chunk');

        $index = IntegerField::new('chunkIndex', '#');

        $contentPreview = TextareaField::new('content', 'Contenuto')
            ->onlyOnIndex()
            ->setMaxLength(200)
            ->setNumOfRows(3);

        $contentFull = TextareaField::new('content', 'Contenuto')
            ->onlyOnDetail()
            ->setNumOfRows(15);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $file, $index, $contentPreview];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $file, $index, $contentFull];
        }

        return [$file, $index, $contentFull];
    }
}
