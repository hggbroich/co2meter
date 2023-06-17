<?php

namespace App\Controller\Admin;

use App\Entity\Device;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DeviceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Device::class;
    }

    public function configureCrud(Crud $crud): Crud {
        return $crud->setEntityLabelInSingular('Gerät')
            ->setEntityLabelInPlural('Geräte');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('mac', 'MAC-Adresse'),
            TextField::new('room', 'Raum'),
            TextField::new('ip', 'letzte IP-Adresse')->setRequired(false)->setFormTypeOption('disabled', true),
            DateTimeField::new('lastSeen', 'Zuletzt gesehen')->onlyOnIndex()->setDisabled()
        ];
    }

}
