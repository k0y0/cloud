<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Hasła muszą się zgadzać.',
                'options' => ['attr' => ['class' => 'form-control']],
                'required' => true,
                'mapped' => false,
                'first_options'  => ['label' => 'Hasło'],
                'second_options' => ['label' => 'Powtórz Hasło'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Nie podano Hasła',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Hasło powinno zawierać minimum {{ limit }} znaków',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ])]
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
