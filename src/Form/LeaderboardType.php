<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Exercice;
use App\Entity\Leaderboard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class LeaderboardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode', ChoiceType::class, [
                'choices' => [
                    'RX' => 'RX',
                    'SCALED' => 'SCALED',
                ],
            ])
            ->add('score')
            ->add('user_id', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('exercice_id', EntityType::class, [
                'class' => Exercice::class,
                'choice_label' => 'description',
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
