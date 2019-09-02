<?php

namespace AppBundle\Form;

use function PHPSTORM_META\type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => true,
                'label_attr' => ['for' => 'email', 'class' => 'sr-only'],
                'attr'  => ['placeholder' => 'Email']
            ])
            ->add('firstName', TextType::class, [
                'label' => true,
                'label_attr' => ['for' => 'First Name', 'class' => 'sr-only'],
                'attr'  => ['placeholder' => 'First Name']
            ])
            ->add('lastName', TextType::class, [
                'label' => true,
                'label_attr' => ['for' => 'Last Name', 'class' => 'sr-only'],
                'attr'  => ['placeholder' => 'Last Name']
            ])
            ->add('phone', TextType::class, [
                'label' => true,
                'label_attr' => ['for' => 'phone', 'class' => 'sr-only'],
                'attr'  => ['placeholder' => 'Phone']
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'  => PasswordType::class,
                'label' => false,
                'first_options'  => [
                    'label' => false,
                    'attr'  => ['placeholder' => 'Password']
                ],
                'second_options' => [
                    'label' => false,
                    'attr'  => ['placeholder' => 'Repeat password']
                ]
            ])
            ->add('birthDate')
            ->add('profileImage', FileType::class, [
                'label'    => 'Choose profile image: ',
                'required' => false
            ])
            ->add('coverImage', FileType::class, [
                'label'    => 'Choose cover image: ',
                'required' => false
            ])
            ->add('sex', ChoiceType::class, [
                'choices' => [
                    'Male'   => 'Male',
                    'Female' => 'Female'
                ]
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

}
