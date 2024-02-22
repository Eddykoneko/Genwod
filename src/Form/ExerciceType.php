<?php

namespace App\Form;

use App\Entity\Exercice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'FOR TIME' => 'FOR TIME',
                    'AMRAP' => 'AMRAP',
                    'EMOM' => 'EMOM',
                ],
            ])
            ->add('description')
            ->add('mode', ChoiceType::class, [
                'choices'  => [
                    'RX' => 'RX',
                    'SCALED' => 'SCALED',
                ],
            ])
            ->add('duree')
            ->add('difficulte', ChoiceType::class, [
                'choices'  => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Difficile' => 'Difficile',
                ],
            ])
            ->add('score',IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exercice::class,
        ]);
    }
}
