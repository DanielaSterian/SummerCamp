<?php


namespace App\Form;


use App\Entity\Activity;
use App\Entity\LicensePlate;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Regex;

class ActivityBlockeeType extends AbstractType
{

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('blocker', TextType::class);

        if($options['oneCar'] == true)
        {
            $builder
                ->add('blockee', EntityType::class, [
                        'class' => LicensePlate::class,
                        'query_builder' => function (EntityRepository $er)
                        {
                            return $er->createQueryBuilder('lp')
                                ->andWhere('lp.user = :val')
                                ->setParameter('val', $this->security->getUser());
                        },
                        'choice_label' => 'license_plate',
                        'disabled' => true]
                );
        }
        elseif ($options['multipleCars'] == true)
        {
            $builder
                ->add('blockee', EntityType::class, [
                    'class' => LicensePlate::class,
                    'query_builder' => function (EntityRepository $er)
                    {
                        return $er->createQueryBuilder('lp')
                            ->andWhere('lp.user = :val')
                            ->setParameter('val', $this->security->getUser());
                    },
                    'choice_label' => 'license_plate',]);
        }

        elseif([$options['noCar'] == true])
        {
            $builder

                ->add('blockee', TextType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'oneCar' => false,
            'multipleCars' =>false,
            'noCar' => false,
        ]);
    }
}