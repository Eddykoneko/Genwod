<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class User1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => [
                'class' => 'form-control',
            /*    'minlength' => '2',
                'maxlength' => '180'
                */
            ],
            'label' => 'Adresse email',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
        ])
        ->add('nom', TextType::class, [
            'attr' => [
                'class' => 'form-control',
            /*    'minlength' => '2',
                'maxlength' => '80'
                */
            ],
            'label' => 'Nom',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                /*    'minlength' => '2',
                    'maxlength' => '80'
                */
                ],
                'label' => 'Prenom',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                /*
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min'=>2, 'max'=> 80])
                ]
                */
                ])
                ->add('age', ChoiceType::class, [
                    'choices' => $this->getAgeChoices(),
                    'placeholder' => 'Choisir un âge',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                    ],
                ])
                ->add('poids', ChoiceType::class, [
                    'choices' => $this->getPoidsChoices(),
                    'placeholder' => 'Choisir un poids',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                    ],
                ])
                ->add('taille', ChoiceType::class, [
                    'choices' => $this->getTailleChoices(),
                    'placeholder' => 'Choisir une taille',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                    ],
                ])
                ->add('genre', ChoiceType::class, [
                    'choices'  => [
                        'Homme' => 'Homme',
                        'Femme' => 'Femme',
                        'Autre' => 'Autre',
                    ],
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                    ],
                ])
                ->add('plainPassword', PasswordType::class, [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password',
                                'class' => 'form-control'
                ],
                    'label' => 'Mot de passe',
                        'label_attr' => [
                            'class' => 'form-label mt-4'
                    ],
                    'constraints' => [
                        new Regex("/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/",
                        "Il faut que le mot de passe contienne au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.")
                    ],
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                    ],
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
