<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichFileField;
use App\Entity\StandBy;
use App\Repository\StandByRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class StandByCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StandBy::class;
    }


    public function __construct(
        protected readonly StandByRepository $standByRepository
    )
    {
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Режим ожидания')
            ->setEntityLabelInSingular('файл')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление файла')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение файла');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield VichFileField::new('mediaFile', 'Файл')
            ->hideOnIndex();

        yield BooleanField::new('view', 'Виден ли файл');

        yield IntegerField::new('sequence', 'Порядок отображения');
    }
}
