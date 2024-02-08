<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Exercice;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaire')
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
            'data_class' => Commentaire::class,
        ]);
    }
}
