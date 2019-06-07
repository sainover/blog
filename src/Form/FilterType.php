<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    Article::STATUS_DECLINED => Article::STATUS_DRAFT,
                    Article::STATUS_MODERATION => Article::STATUS_MODERATION,
                    Article::STATUS_PUBLISHED => Article::STATUS_PUBLISHED,
                ],
                'placeholder' => 'Set status',
            ])
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
            ])
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
