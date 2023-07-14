<?php

namespace App\Form;

use App\Entity\Visual;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VisualType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, [
                'attr' => [
                    'placeholder' => 'URL du visuel'
                ]
            ])
            ->add('caption', TextType::class, [
                'attr' => [
                    'placeholder' => 'Titre du visuel'
                ]
            ])
            ->add('visualKind', ChoiceType::class, [
                'choices' => [
                    'Photo' => 'photo',
                    'Vidéo' => 'video',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visual::class,
        ]);
    }
}
