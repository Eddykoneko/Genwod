<?php

namespace App\Form;

use App\Entity\Exercice;
use App\Entity\HistoriqueExercice;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HistoriqueExerciceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode')
            ->add('nombreTours')
            ->add('nombreRepetition')
            ->add('temps')
            ->add('commentaire')
            ->add('user_id', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('exercice_id', EntityType::class, [
                'class' => Exercice::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HistoriqueExercice::class,
        ]);
    }
}
