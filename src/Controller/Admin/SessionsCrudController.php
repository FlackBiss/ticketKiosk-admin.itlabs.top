<?php

namespace App\Controller\Admin;

use App\Entity\Sessions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SessionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sessions::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Сеансы')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::NEW, Action::DELETE, Action::EDIT);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addCssFile('/css/admin.css');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield DateTimeField::new('startAt', 'Начало')->hideOnForm();
        yield DateTimeField::new('endAt', 'Окончание')->hideOnForm();

        yield IntegerField::new('deltaTime', 'Продолжительность')
            ->formatValue(function ($value) {
                return $value . ' сек.';
            });
        yield AssociationField::new('terminal', 'Терминал');
        yield IntegerField::new('events', 'Событий')
            ->formatValue(function ($value) {
                return count($value);
            });
        yield AssociationField::new('events', false)
            ->setFormType(EntityType::class)
            ->setTemplatePath('admin/field/sessions.html.twig');
    }

}
