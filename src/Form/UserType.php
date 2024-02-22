<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('isAdmin', CheckboxType::class, [
                'label' => 'Administrateur',
                'mapped' => false,
                'required' => false,
                ])
            ->add('nom')
            ->add('prenom')
            //je veux mettre le mot de passe mais chiffre
            ->add('plainPassword', PasswordType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'label' => 'Mot de passe',
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                    new Regex("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/",
                    "Il faut que le mot de passe contienne au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.")
                ],
            ])
            ->add('createdAt')
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
            ])
            ->add('genre', ChoiceType::class, [
                'choices'  => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Est Vérifié',
                'required' => false,
            ])
            
        ;
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
