<?php

namespace App\Form;

use App\Form\StatusType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('status', StatusType::class)
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false
            // Configure your form options here
        ]);
    }
}
