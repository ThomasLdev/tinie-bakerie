<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\IngredientGroupTranslation;
use App\Services\Locale\Locales;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<IngredientGroupTranslation>
 */
class IngredientGroupTranslationType extends AbstractType
{
    public function __construct(private readonly Locales $locales)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->locales->get();

        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => array_combine($locales, $locales),
                'label' => 'admin.global.locale',
                'required' => true,
                'disabled' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('label', TextType::class, [
                'label' => 'admin.ingredient_group.label.title',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'empty_data' => '',
                'help' => 'admin.ingredient_group.label.help',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IngredientGroupTranslation::class,
            'translation_domain' => 'admin',
        ]);
    }
}
