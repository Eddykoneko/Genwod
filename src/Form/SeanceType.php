<?php

namespace App\Form;

use App\Entity\Seance;
use App\Enum\Mode;
use App\Enum\WodType;
use App\Form\DataTransformer\TempsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Saisie d'un résultat. Les champs de score s'adaptent au format du WOD.
 */
/**
 * @extends AbstractType<Seance>
 */
class SeanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var WodType $type */
        $type = $options['wod_type'];

        $builder
            ->add('realiseeLe', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('mode', EnumType::class, [
                'class' => Mode::class,
                'label' => 'Version',
                'choice_label' => static fn (Mode $m) => $m->label(),
                'expanded' => true,
                'help' => 'RX : charges et mouvements prescrits. Scaled : version adaptée.',
            ]);

        match ($type) {
            WodType::ForTime => $builder->add(
                $builder->create('tempsSecondes', TextType::class, [
                    'label' => 'Temps final',
                    'attr' => ['placeholder' => 'mm:ss', 'inputmode' => 'numeric', 'pattern' => '[0-9:]*', 'data-chrono-cible' => 'temps'],
                ])->addModelTransformer(new TempsTransformer()),
            ),
            WodType::Amrap => $builder
                ->add('tours', IntegerType::class, ['label' => 'Tours complets', 'attr' => ['min' => 0, 'inputmode' => 'numeric']])
                ->add('reps', IntegerType::class, ['label' => '+ répétitions', 'required' => false, 'attr' => ['min' => 0, 'inputmode' => 'numeric']]),
            WodType::DeathBy => $builder
                ->add('tours', IntegerType::class, ['label' => 'Minutes complétées', 'attr' => ['min' => 0, 'inputmode' => 'numeric']])
                ->add('reps', IntegerType::class, ['label' => '+ reps de la minute ratée', 'required' => false, 'attr' => ['min' => 0, 'inputmode' => 'numeric']]),
            WodType::ForLoad => $builder->add('chargeKg', NumberType::class, [
                'label' => 'Charge (kg)',
                'scale' => 1,
                'html5' => true,
                'attr' => ['min' => 0, 'step' => 0.5, 'inputmode' => 'decimal', 'data-chrono-cible' => 'charge'],
            ]),
            default => $builder->add('reps', IntegerType::class, ['label' => 'Total de répétitions', 'attr' => ['min' => 0, 'inputmode' => 'numeric', 'data-chrono-cible' => 'reps']]),
        };

        $builder
            ->add('ressenti', ChoiceType::class, [
                'label' => 'Ressenti',
                'required' => false,
                'expanded' => true,
                'placeholder' => false,
                'choices' => ['Facile' => 1, 'Correct' => 2, 'Dur' => 3, 'Très dur' => 4, 'Au max' => 5],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['rows' => 2, 'maxlength' => 500, 'placeholder' => 'Charges utilisées, adaptations…'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Seance::class]);
        $resolver->setRequired('wod_type');
        $resolver->setAllowedTypes('wod_type', WodType::class);
    }
}
