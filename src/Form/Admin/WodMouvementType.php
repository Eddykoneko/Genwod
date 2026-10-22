<?php

namespace App\Form\Admin;

use App\Entity\Mouvement;
use App\Entity\WodMouvement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** Une ligne de WOD dans le formulaire d'administration. *.
 * @extends AbstractType<WodMouvement>
 */
class WodMouvementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, ['label' => 'Ordre'])
            ->add('mouvement', EntityType::class, [
                'class' => Mouvement::class,
                'choice_label' => 'nom',
                'group_by' => static fn (Mouvement $m) => $m->getCategorie()->label(),
            ])
            ->add('quantite', IntegerType::class, ['label' => 'Quantité', 'help' => '0 = « Max »'])
            ->add('note', TextType::class, ['label' => 'Charge / note', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => WodMouvement::class]);
    }
}
