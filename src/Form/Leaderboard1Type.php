<?php

namespace App\Form;

use App\Entity\Exercice;
use App\Entity\Leaderboard;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Leaderboard1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode', null, [
                'label' => 'Mode',
                'attr' => [
                    'placeholder' => 'Entrez le mode (RX ou SCALED)',
                ],
            ])
            ->add('score', null, [
                'label' => 'Score',
                'attr' => [
                    'placeholder' => 'Entrez le score',
                ],
            ])
            ->add('user_id', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // Affiche l'email de l'utilisateur
                'label' => 'Utilisateur',
                'placeholder' => 'Sélectionnez un utilisateur',
            ])
            ->add('exercice_id', EntityType::class, [
                'class' => Exercice::class,
                'choice_label' => 'description', // Affiche la description de l'exercice
                'label' => 'Exercice',
                'placeholder' => 'Sélectionnez un exercice',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Leaderboard::class,
        ]);
    }
}