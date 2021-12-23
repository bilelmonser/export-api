<?php 

namespace App\DataFixtures;

use App\Entity\User;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
    }

    private function loadUsers(ObjectManager $manager)
    {
        foreach ($this->getUserData() as [$firstname,$lastname, $password, $email, $roles]) {
            $user = new User();
            $user->setFirstname($firstname);
			$user->setLastname($lastname);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);
            $manager->persist($user);
            $this->addReference($email, $user);
        }
        $manager->flush();
    }

    private function getUserData($quantity = 10): array
    {

        $data = [];
        for($i=0; $i<$quantity ;$i++) {
            array_push($data, [
				"first admin 2",
				"last admin 2",
                'secret2',
                "admin2@admin.com",
                ['ROLE_USER'],
            ]);
			break;
        }
        return $data;
    }
}