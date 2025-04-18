<?php

namespace App\Form;

use App\Entity\Foiling;
use App\Service\FoilingHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardFilterFormType extends AbstractType
{
    private FoilingHelper $foilingHelper;

    public function __construct(FoilingHelper $foilingHelper)
    {
        $this->foilingHelper = $foilingHelper;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $foilings = $this->foilingHelper->getAllFoilings();
        $foilings = array_flip($foilings->toArray());
        $foilings = array_merge([
                FoilingHelper::NO_FILTER_DESC => FoilingHelper::NO_FILTER_KEY,
                FoilingHelper::PLACEHOLDER_DESC => FoilingHelper::PLACEHOLDER_KEY,
            ], 
        $foilings);

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
                'choices' => $foilings,
                'choice_attr' => [
                    'Filter on foiling' => ['disabled' => 'true', 'selected' => 'true', 'hidden' => 'true'],
                   
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
        ]);
    }
}
