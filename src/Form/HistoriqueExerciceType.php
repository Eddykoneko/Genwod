<?php

namespace App\Form;


use App\Entity\HistoriqueExercice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class HistoriqueExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode', ChoiceType::class, [
                'choices'  => [
                    'Mode' => [
                        'RX' => 'RX',
                        'SCALED' => 'SCALED',
                    ],
                ],
            ])
            ->add('nombreTours')

            ->add('nombreRepetition')

            ->add('temps', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'hours' => range(0, 1), // Permet les heures de 0 à 1 (pour un total de 2 heures)
                'minutes' => range(0, 59), // Permet toutes les minutes
                'seconds' => range(0, 59), // Permet toutes les secondes
                'with_seconds' => true,
                ],
            )
            ->add('commentaire')
//                 'class' => User::class,
// 'choice_label' => 'id',
//             ])
//             ->add('exercice_id', EntityType::class, [
//                 'class' => Exercice::class,
// 'choice_label' => 'id',
//             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HistoriqueExercice::class,
        ]);
    }

}
