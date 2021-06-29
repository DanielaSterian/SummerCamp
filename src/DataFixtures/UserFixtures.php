<?php


namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function load(ObjectManager $manager)
    {
        $users[0] = new User();
        $users[0]->setPassword($this->passwordEncoder->encodePassword(
            $users[0],
            'Bog!45'
        ));
        $users[0]->setFirstName('Daniela')
            ->setLastName('Sterian')
            ->setEmail('daniela@gmail.com')
            ->setRoles(['ROLE_USER']);
        $manager->persist($users[0]);

        $users[1] = new User();
        $users[1]->setPassword($this->passwordEncoder->encodePassword(
            $users[1],
            '1234'
        ));
        $users[1]->setFirstName('Dan')
            ->setLastName('Ster')
            ->setEmail('dan@gmail.com')
            ->setRoles(['ROLE_USER']);
        $manager->persist($users[1]);

        $manager->flush();
    }
}