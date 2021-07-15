<?php


namespace App\Form;


use App\Entity\User;
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
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['forUsual'] == true) {
            $builder
                ->add('email', EmailType::class, [
                    'label' => 'Email',
                    'attr' => [
                        'class' => 'form-control']
                ])
                ->add('firstName', TextType::class, [
                    'label' => 'First Name',
                    'attr' => [
                        'class' => 'form-control']
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'First Name',
                    'attr' => [
                        'class' => 'form-control']
                ])
                ->add('imageFile', FileType::class, [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]);
        }


        if ($options['forPass'] == true) {
            $builder
                ->add('currentPassword', PasswordType::class, [
                    'mapped' => false,
                    'label' => 'Current Password',
                    'attr' => [
                        'class' => 'form-control']
                ])
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Password',
                        'attr' => ['class' => 'form-control']],
                    'second_options' => ['label' => 'Repeat Password',
                        'attr' => ['class' => 'form-control']],
//                    'constraints' => [new Length(['min' => 5, 'max' => 20])]
                ]);
        }
        $builder
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-lg btn-warning'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'forPass' => false,
            'forUsual' => false,
        ]);
    }
}