<?php

namespace App\Controller\Admin;

use App\Entity\IpNetwork;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class IpNetworkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return IpNetwork::class;
    }

    public function configureCrud(Crud $crud): Crud {
        return $crud->setEntityLabelInSingular('IP-Netzwerk')
            ->setEntityLabelInPlural('IP-Netzwerke');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Name'),
            TextField::new('cidr', 'CIDR-Notation des Netzwerk')
        ];
    }

}
