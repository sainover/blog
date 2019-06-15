<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null, [
                'attr' => [
                    'autocomplete' => 'off',
                    'placeholder' => 'User email',
                    'class' => 'autocomplete_input',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Article::STATUSES_VIEWABLE_TO_ADMIN,
                'placeholder' => 'Set status',
            ])
            ->add('dateFrom', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('dateTo', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('Search', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            // Configure your form options here
        ]);
    }
}
