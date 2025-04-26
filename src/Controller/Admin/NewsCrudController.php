<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichFileField;
use App\Controller\Admin\Field\VichImageField;
use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class NewsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return News::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Новости')
            ->setEntityLabelInSingular('новость')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление новости')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение новости');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield VichImageField::new('imageFile', 'Изображение')
            ->hideOnIndex();

        yield TextField::new('title', 'Заголовок');
        yield TextEditorField::new('description', 'Описание');
        yield DateTimeField::new('dateTimeAt', 'Дата и время');
    }
}
