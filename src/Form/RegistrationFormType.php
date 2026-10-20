<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<User>
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'attr' => ['autocomplete' => 'email', 'placeholder' => 'vous@exemple.fr'],
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'help' => 'Affiché dans les classements. 3 à 30 caractères.',
                'attr' => ['autocomplete' => 'username', 'placeholder' => 'ex. RowingQueen'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'help' => '10 caractères minimum. Une phrase de passe est idéale.',
                    'attr' => ['autocomplete' => 'new-password', 'data-controller' => 'force-mdp'],
                    'constraints' => [
                        new Assert\NotBlank(message: 'Choisissez un mot de passe.'),
                        new Assert\Length(min: 10, max: 4096, minMessage: 'Au moins {{ limit }} caractères.'),
                        new Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_MEDIUM, message: 'Ce mot de passe est trop facile à deviner.'),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte les conditions d\'utilisation et la politique de confidentialité',
                'constraints' => [new Assert\IsTrue(message: 'Vous devez accepter les conditions pour vous inscrire.')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
