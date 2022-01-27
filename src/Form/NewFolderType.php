<?php

namespace App\Form;

use App\Entity\Folder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewFolderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hasParent', HiddenType::class, [
                "mapped" => false
            ])
            ->add('parentId', HiddenType::class, [
                "mapped" => false
            ])
            ->add('folderName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => "Nowy Folder"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
        ]);
    }
}
