<?php

namespace App\Controller\Admin;

use App\Entity\Wod;
use App\Enum\Difficulte;
use App\Enum\WodType;
use App\Form\Admin\WodMouvementType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

/** @extends AbstractCrudController<Wod> */
final class WodCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Wod::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('WOD')
            ->setEntityLabelInPlural('WODs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['titre', 'consignes']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $voir = Action::new('voir', 'Voir sur le site', 'fa fa-eye')
            ->linkToUrl(fn (Wod $wod) => $this->generateUrl('app_wod_show', ['slug' => $wod->getSlug()]));

        return $actions->add(Crud::PAGE_INDEX, $voir)->add(Crud::PAGE_DETAIL, $voir);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('type')->setChoices(WodType::choices()))
            ->add(ChoiceFilter::new('difficulte')->setChoices(Difficulte::choices()))
            ->add(BooleanFilter::new('officiel'))
            ->add(BooleanFilter::new('public'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('titre');
        yield ChoiceField::new('type')->renderAsBadges();
        yield ChoiceField::new('difficulte', 'Difficulté')->renderAsBadges(static fn (mixed $valeur) => match ($valeur instanceof Difficulte ? $valeur : Difficulte::tryFrom((string) $valeur)) {
            Difficulte::Facile => 'success',
            Difficulte::Difficile => 'danger',
            default => 'warning',
        });
        yield IntegerField::new('duree', 'Durée (min)');
        yield IntegerField::new('tours')->setHelp('For Time uniquement (1 = chipper).')->hideOnIndex();
        yield TextareaField::new('consignes')->hideOnIndex();
        yield CollectionField::new('lignes', 'Mouvements')
            ->setEntryType(WodMouvementType::class)
            ->setFormTypeOption('by_reference', false)
            ->onlyOnForms();
        yield CollectionField::new('lignes', 'Mouvements')->onlyOnDetail();
        yield AssociationField::new('auteur')->hideOnForm();
        yield BooleanField::new('officiel');
        yield BooleanField::new('public');
        yield IntegerField::new('likesCount', '♥')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
    }
}
