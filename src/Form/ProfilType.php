<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\Niveau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<User>
 */
class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, ['label' => 'Pseudo'])
            ->add('niveau', EnumType::class, [
                'class' => Niveau::class,
                'label' => 'Niveau',
                'choice_label' => static fn (Niveau $n) => $n->label(),
                'help' => 'Sert à pré-régler la difficulté du générateur.',
                'expanded' => true,
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'required' => false,
                'attr' => ['rows' => 2, 'maxlength' => 160, 'placeholder' => 'Box, objectifs, mouvement préféré…'],
            ])
            ->add('taille', IntegerType::class, ['label' => 'Taille (cm)', 'required' => false])
            ->add('poids', IntegerType::class, ['label' => 'Poids (kg)', 'required' => false, 'help' => 'Privé : jamais affiché publiquement.']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
