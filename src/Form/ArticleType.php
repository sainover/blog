<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Article;
use App\Entity\Tag;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Header text input',
                ]
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'expanded' => false,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'tags article_tags form-control',
                    'data-placeholder' => 'Add some tags',
                ]
            ])
            ->add('content', CKEditorType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
