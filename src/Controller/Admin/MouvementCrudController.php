<?php

namespace App\Controller\Admin;

use App\Entity\Mouvement;
use App\Enum\Categorie;
use App\Enum\Equipement;
use App\Enum\Unite;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

/** @extends AbstractCrudController<Mouvement> */
final class MouvementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Mouvement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mouvement')
            ->setEntityLabelInPlural('Mouvements')
            ->setDefaultSort(['categorie' => 'ASC', 'nom' => 'ASC'])
            ->setPaginatorPageSize(50);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('categorie')->setChoices(self::choix(Categorie::cases())))
            ->add(ChoiceFilter::new('equipement')->setChoices(self::choix(Equipement::cases())));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom');
        yield SlugField::new('slug')->setTargetFieldName('nom')->hideOnIndex();
        yield ChoiceField::new('categorie', 'Catégorie')->renderAsBadges();
        yield ChoiceField::new('equipement', 'Matériel');
        yield ChoiceField::new('unite', 'Unité')->setChoices(['Répétitions' => Unite::Reps, 'Calories' => Unite::Calories, 'Mètres' => Unite::Metres, 'Secondes' => Unite::Secondes])->setFormType(EnumType::class)->setFormTypeOption('class', Unite::class);
        yield IntegerField::new('volumeBase', 'Volume de base')->setHelp('Pour un tour en difficulté intermédiaire.');
        yield TextField::new('chargeRx', 'Charge RX');
        yield TextareaField::new('description')->hideOnIndex();
        yield BooleanField::new('technique')->setHelp('Exclu en difficulté facile et en Tabata.');
        yield BooleanField::new('actif')->setHelp('Proposé par le générateur.');
    }

    /**
     * @param list<Categorie|Equipement> $cases
     *
     * @return array<string, Categorie|Equipement>
     */
    private static function choix(array $cases): array
    {
        $choix = [];
        foreach ($cases as $case) {
            $choix[$case->label()] = $case;
        }

        return $choix;
    }
}
