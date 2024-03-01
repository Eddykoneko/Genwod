<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class User1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('nom')
            ->add('prenom')
            // ->add('mot_de_passe', TextType::class, [
            //     'mapped' => false,
            // ])
            ->add('age', ChoiceType::class, [
                'choices' => $this->getAgeChoices(),
                'placeholder' => 'Choisir un âge',
            ])
            ->add('poids', ChoiceType::class, [
                'choices' => $this->getPoidsChoices(),
                'placeholder' => 'Choisir un poids',
            ])
            ->add('taille', ChoiceType::class, [
                'choices' => $this->getTailleChoices(),
                'placeholder' => 'Choisir une taille',
            ]);
    }

    private function getAgeChoices()
    {
        $ages = [];
        for ($i = 18; $i <= 100; $i++) {
            $ages[$i] = $i;
        }
        return $ages;
    }

    private function getPoidsChoices()
    {
        $poids = [];
        for ($i = 40; $i <= 150; $i++) {
            $poids[$i] = $i;
        }
        return $poids;
    }

    private function getTailleChoices()
    {
        $tailles = [];
        for ($i = 120; $i <= 220; $i++) {
            $tailles[$i] = $i;
        }
        return $tailles;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
