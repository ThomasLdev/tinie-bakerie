<?php

namespace App\Form;

use App\Entity\PostSection;
use App\Services\PostSection\Enum\PostSectionType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostSectionFormType extends AbstractType
{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')] private readonly string $supportedLocales,
        #[Autowire(param: 'default_locale')] private readonly string $defaultLocale,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Default' => PostSectionType::Default,
                    'Two Columns' => PostSectionType::TwoColumns,
                    'Two Columns Media Left' => PostSectionType::TwoColumnsMediaLeft,
                ],
                'choice_value' => function (?PostSectionType $type) {
                    return $type?->value;
                },
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'required' => true,
                'attr' => ['rows' => 12]
            ]);


        foreach (explode('|', $this->supportedLocales) as $locale) {
            $isDefault = $locale === $this->defaultLocale;

            $builder
                ->add(
                    $isDefault ? 'content' : sprintf('%s_%s', 'content', $locale),
                    TextareaType::class,
                    [
                        'label' => $locale,
                        'required' => true,
                        'mapped' => $isDefault,
                        'attr' => ['rows' => 12]
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostSection::class,
        ]);
    }
}
