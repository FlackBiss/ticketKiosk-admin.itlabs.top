<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichImageField;
use App\Entity\Scheme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
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
            ->setFormThemes(['admin/field/schemaNew.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
            ->setEntityLabelInPlural('Схемы зала')
            ->setEntityLabelInSingular('схему зала')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление схемы зала')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение схемы зала');
    }

    public function configureAssets(Assets $assets): Assets
    {
        $assets->addWebpackEncoreEntry('schemaNew');
        return parent::configureAssets($assets);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Главная');
        yield IdField::new('id')
            ->hideOnForm();
        yield TextField::new('name', 'Название');
        yield VichImageField::new('imageFile', 'Изображение')
            ->setRequired(true)
            ->setFormTypeOption('allow_delete', false)
            ->onlyOnForms()
            ->hideWhenUpdating();
        yield VichImageField::new('image', 'Изображение')
            ->setRequired(true)
            ->setFormTypeOption('allow_delete', false)
            ->hideOnForm();

        yield HiddenField::new('image', 'Изображение')
            ->onlyOnForms();

        yield FormField::addTab('Схема зала');
        yield HiddenField::new('schemeWidget')
            ->setFormTypeOptions([
                'block_name' => 'schemaNew',
            ])
            ->onlyOnForms();
        yield HiddenField::new('schemeData')
            ->onlyOnForms();
    }
}
