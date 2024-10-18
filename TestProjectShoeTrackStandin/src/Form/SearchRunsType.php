<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchRunsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Start Date',
                'required' => true,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'End Date',
                'required' => true,
            ])
            ->add('shoe', ChoiceType::class, [
                'choices' => $options['shoes'], // Passed as options in the controller
                'choice_label' => function($shoe) {
                    return $shoe->getNameBrandModel(); // Customize this based on your shoe entity
                },
                'label' => 'Filter by Shoe (Optional)',
                'required' => false,
                'placeholder' => 'All Shoes',
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'shoes' => [], // Expect this to be passed from the controller
        ]);
    }
}