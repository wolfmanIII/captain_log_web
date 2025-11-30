<?php

namespace App\Controller\Admin;

use App\Entity\DocumentFile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DocumentFileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DocumentFile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Documento indicizzato')
            ->setEntityLabelInPlural('Documenti indicizzati')
            ->setDefaultSort(['path' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        // NEW/EDIT non hanno senso: i DocumentFile li gestisce il command
        // DELETE esiste già (INDEX e DETAIL) → NON va riaggiunto
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id')->hideOnForm();

        $path = TextField::new('path', 'Path')
            ->setHelp('Percorso relativo in var/knowledge');

        $ext = TextField::new('extension', 'Ext')
            ->setMaxLength(10);

        $hash = TextField::new('hash', 'Hash')
            ->setMaxLength(64)
            ->setHelp('SHA256 del file')
            ->hideOnForm();

        $indexedAt = DateTimeField::new('indexedAt', 'Indicizzato il')
            ->setFormat('yyyy-MM-dd HH:mm');

        // NOTA: usiamo un campo virtuale legato a "id" e non a "chunks"
        $chunksCount = \EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField::new('id', 'Chunks')
            ->onlyOnIndex()
            ->formatValue(function ($value, $entity) {
                /** @var \App\Entity\DocumentFile $entity */
                return $entity->getChunks()->count();
            });

        $chunksAssoc = AssociationField::new('chunks', 'Chunk')
            ->onlyOnDetail();

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $path, $ext, $hash, $indexedAt, $chunksCount];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $path, $ext, $hash, $indexedAt, $chunksAssoc];
        }

        return [$path, $ext, $hash, $indexedAt];
    }

}
