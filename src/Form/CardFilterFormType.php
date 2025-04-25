<?php

namespace App\Form;

use App\Service\FoilingHelper;
use App\Service\RarityHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardFilterFormType extends AbstractType
{
    public function __construct(
        private FoilingHelper $foilingHelper,
        private RarityHelper $rarityHelper,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('foiling', ChoiceType::class, [
                'attr' => [
                    'class' =>'form-select'
                ],
                'placeholder' => FoilingHelper::PLACEHOLDER_DESC,
                'placeholder_attr' => [
                    'disabled' => 'true',
                    // 'hidden' => 'true',
                ],
                'choices' => $this->configureFoilingChoicesBasedOnOptions($options),
                'choice_attr' => [
                    FoilingHelper::PLACEHOLDER_DESC => ['disabled' => 'true', 'selected' => 'true', 'hidden' => 'true'],

                ],
                'required' => false,
            ])
            ->add('rarity', ChoiceType::class, [
                'attr' => [
                    'class' =>'form-select'
                ],
                'placeholder' => RarityHelper::PLACEHOLDER_DESC,
                'placeholder_attr' => [
                    'disabled' => 'true',
                    // 'hidden' => 'true',
                ],
                'choices' => $this->configureRarityChoices(),
                'choice_attr' => [
                    RarityHelper::PLACEHOLDER_DESC => ['disabled' => 'true', 'selected' => 'true', 'hidden' => 'true'],

                ],
                'required' => false,
            ])
            ->add('cardName', TextType::class, [
                'attr' => [
                    'placeholder' =>'Filter on card name',
                    'class' => 'form-control'
                ],
                'label' => 'Filter on card name',
                'required' => false,
            ])
            ->add('hide', CheckboxType::class, [
                'attr' => [
                    'class' =>'form-check-input',
                    'role' => 'switch'
                ],
                'label' => 'Hide completed playsets',
                'required' => false,
            ])
            ->add('collectorView', CheckboxType::class, [
                'attr' => [
                    'class' =>'form-check-input',
                    'role' => 'switch'
                ],
                'label' => 'Show expanded \'collector view\'',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'csrf_protection' => false,
            'csrf_protection' => true,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_token',
            // an arbitrary string used to generate the value of the token
            // using a different string for each form improves its security
            'csrf_token_id'   => 'foil_filter',
            'attr' => [
                'class' => 'mb-3'
            ],
            'promoView' => false,
        ]);

        $resolver->setAllowedTypes('promoView', 'bool');
    }

    /**
     * Fetch all foilings from the database and apply some custom magic:
     * - reverse sort alphabetically on key (so it's Standard -> RF -> CF)
     * - remove Gold Cold Foil from the list if not browsing Promos
     * - add some helper options ('No Filter', and a placeholder)
     * - lastly, flip the keys/values so the list is useable as select options
     */
    private function configureFoilingChoicesBasedOnOptions(array $options): array
    {
        $foilings = $this->foilingHelper->getAllFoilings();
        $foilings = $foilings->toArray();
        krsort($foilings);

        if (false === $options['promoView']) {
            unset($foilings['G']);
        }
        $foilings = array_flip($foilings);
        $foilings = array_merge(
            [
                FoilingHelper::NO_FILTER_DESC => FoilingHelper::NO_FILTER_KEY,
                FoilingHelper::PLACEHOLDER_DESC => FoilingHelper::PLACEHOLDER_KEY,
            ],
            $foilings
        );

        return $foilings;
    }

    /**
     * Manually configre the rarity choices, as the order is so specific.
     */
    private function configureRarityChoices(): array
    {
        return [
            RarityHelper::NO_FILTER_DESC => RarityHelper::NO_FILTER_KEY,
            RarityHelper::PLACEHOLDER_DESC => RarityHelper::PLACEHOLDER_KEY,
            "Fabled" => "F",
            "Legendary" => "L",
            "Marvel" => "V",
            "Majestic" => "M",
            "Super Rare" => "S",
            "Rare" => "R",
            "Common" => "C",
            "Token" => "T",
            "Promo" => "P",
        ];
    }
}
