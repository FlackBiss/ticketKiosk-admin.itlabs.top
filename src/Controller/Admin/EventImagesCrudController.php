<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichImageField;
use App\Entity\EventImages;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EventImagesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventImages::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield VichImageField::new('imageFile', 'Изображение')
            ->setRequired(false)
            ->setFormTypeOption('allow_delete', false);
    }
}
