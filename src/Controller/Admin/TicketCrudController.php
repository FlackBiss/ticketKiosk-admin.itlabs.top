<?php

namespace App\Controller\Admin;

use App\Entity\Ticket;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TicketCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ticket::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Билеты')
            ->setEntityLabelInSingular('билет')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление билета')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение билета');
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters->add('price');
        $filters->add('type');
        $filters->add('event');

        return parent::configureFilters($filters);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('place', 'Место');
        yield IntegerField::new('price', 'Цена');
        yield AssociationField::new('event', 'Мероприятие');
        yield TextField::new('type', 'Тип');
        yield DateTimeField::new('createdAt', 'Дата создания');
        yield TextField::new('email', 'Email');
        yield TextField::new('surname', 'Фамилия');
        yield TextField::new('name', 'Имя');
    }
}
