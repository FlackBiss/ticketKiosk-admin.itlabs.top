<?php

namespace App\Controller\Admin;

use App\Entity\Terminal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class TerminalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Terminal::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Терминалы')
            ->setEntityLabelInSingular('терминал')
            ->setPageTitle(Crud::PAGE_NEW, 'Добавление терминала')
            ->setPageTitle(Crud::PAGE_EDIT, 'Изменение терминала');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/auto_update_kkt_network.js');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->hideOnForm();
        yield IntegerField::new('password', 'Пароль')
            ->onlyOnForms()
            ->setColumns(8);
        yield TextField::new('title', 'Название терминала')
            ->setColumns(8);

        yield FormField::addRow();

        yield TextField::new('ipAddress', 'IP-адрес')
            ->setColumns(8);

        yield FormField::addRow();

        yield TextEditorField::new('contacts', 'Контакты')
            ->setColumns(8);

        yield TextField::new('isNetworkStringify', 'В сети')
            ->renderAsHtml()
            ->onlyOnIndex();

        yield TextField::new('isKktStringify', 'Есть бумага')
            ->renderAsHtml()
            ->onlyOnIndex();
    }
}