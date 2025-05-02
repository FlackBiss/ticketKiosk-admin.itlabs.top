<?php

namespace App\Controller\Admin;

use App\Entity\Scheme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MapField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SchemeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Scheme::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Схемы зала')
            ->setEntityLabelInSingular('схему зала')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление схемы зала')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение схемы зала');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();
        yield TextField::new('name', 'Название');
        yield MapField::new('scheme', 'Схема зала')
            ->setObjectIdentifierPropertyName('id')
            ->setObjectTitlePropertyName('name')
            ->setObjectMapPropertyName('scheme')
            ->setMapObjectsPropertyName('places');
    }
}
