<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Contracts\Localized;
use App\Services\Locale\Locales;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Renders a fixed-cardinality collection of translations (one entry per
 * supported locale). Replaces the boilerplate that every `*Type` parent
 * used to repeat: PRE_SET_DATA seeding, empty_data closure, and
 * `supported_locales` plumbing.
 *
 * @extends AbstractType<iterable<Localized>>
 */
final class TranslationsCollectionType extends AbstractType
{
    public function __construct(private readonly Locales $locales)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locales = $this->locales->get();
        /** @var class-string<Localized> $translationClass */
        $translationClass = $options['translation_class'];

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($locales, $translationClass): void {
            $data = $event->getData();

            if ($data instanceof Collection && !$data->isEmpty()) {
                return;
            }

            if (\is_array($data) && [] !== $data) {
                return;
            }

            $seeded = new ArrayCollection();

            foreach ($locales as $locale) {
                $seeded->add(new $translationClass()->setLocale($locale));
            }

            $event->setData($seeded);
        });
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_add' => false,
            'allow_delete' => false,
            'by_reference' => false,
            'required' => true,
            'label' => 'admin.global.translations',
            'translation_domain' => 'admin',
        ]);

        $resolver->setRequired(['entry_type', 'translation_class']);
        $resolver->setAllowedTypes('translation_class', 'string');
    }

    #[\Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
