<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Мероприятия')
            ->setEntityLabelInSingular('мероприятие')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление мероприятия')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение мероприятия');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();

        yield TextField::new('title', 'Название');
        yield TextEditorField::new('description', 'Описание');

        yield DateTimeField::new('dateTimeAt', 'Дата и время');

        yield ChoiceField::new('type', 'Тип')
            ->setChoices(
                [
                    'Неограниченное количество мест' => 'Неограниченное количество мест',
                    'Ограниченное количество мест' => 'Ограниченное количество мест',
                    'Места согласно билетам' => 'Места согласно билетам',
                ]
            );
        yield TextField::new('age', 'Возраст');

        yield TimeField::new('duration', 'Длительность');

        yield IntegerField::new('places', 'Места (количество)');

        yield AssociationField::new('news', 'Новости')
            ->hideOnForm();

        yield AssociationField::new('scheme', 'Схема зала');

        yield NumberField::new('price', 'Цена');

        yield AssociationField::new('tickets', 'Билеты')
            ->hideOnForm();
    }
}
