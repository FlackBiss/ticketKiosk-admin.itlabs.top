<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichFileField;
use App\Controller\Admin\Field\VichImageField;
use App\Entity\Information;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InformationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Information::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Информация')
            ->setEntityLabelInSingular('информацию')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление информации')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение информации');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        $actions->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN);
        $actions->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

        return parent::configureActions($actions);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield TextEditorField::new('description', 'Описание')
            ->setColumns(8);
        yield VichImageField::new('imageFile', 'Изображение')
            ->hideOnIndex()
            ->setColumns(8);
    }
}
