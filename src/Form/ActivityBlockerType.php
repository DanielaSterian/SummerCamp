<?php


namespace App\Form;


use App\Entity\Activity;
use App\Entity\LicensePlate;
use App\Repository\LicensePlateRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityBlockerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['oneCar'] == true)
        {
            $builder
                ->add('blocker', EntityType::class, [
                        'class' => LicensePlate::class,
                        'query_builder' => function (LicensePlateRepository $repo)
                        {
                            return $repo->findUserLP();
                        },
                        'choice_label' => 'license_plate',
                        'disabled' => true]
                );
        }
        elseif ($options['multipleCars'] == true)
        {
            $builder
                ->add('blocker', EntityType::class, [
                    'class' => LicensePlate::class,
                    'query_builder' => function (LicensePlateRepository $repo)
                    {
                        return $repo->findUserLP();
                    },
                    'choice_label' => 'license_plate',]);
        }
        $builder
            ->add('blockee', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'oneCar' => false,
            'multipleCars' =>false,
        ]);
    }
}