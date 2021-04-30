<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Guest;
use App\Entity\Contact;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /*****    CREATING USERS    *****/

        // user #1 (alphonce)
        $alphonce = new User();
        $alphonce->setName('Alphonce');
        $alphonce->setRoles(['ROLE_USER']);
        $alphonce->setEmail('alphonce@pots.fr');
        $alphonce->setActive(true);
        $alphonce->setPassword(password_hash('alphonce', PASSWORD_DEFAULT));
        $manager->persist($alphonce);

        // user #2 (marie)
        $marie = new User();
        $marie->setName('Marie');
        $marie->setRoles(['ROLE_USER']);
        $marie->setEmail('marie@pots.fr');
        $marie->setActive(true);
        $marie->setPassword(password_hash('marie', PASSWORD_DEFAULT));
        $manager->persist($marie);

        // user #2 (Judith)
        $judith = new User();
        $judith->setName('judith');
        $judith->setRoles(['ROLE_USER']);
        $judith->setEmail('judith@pots.fr');
        $judith->setActive(true);
        $judith->setPassword(password_hash('judith', PASSWORD_DEFAULT));
        $manager->persist($judith);

        /*****    CREATING CONTACTS    *****/

        $marie_and_alphonce_are_friends = new Contact();
        $marie_and_alphonce_are_friends->setUser1($marie);
        $marie_and_alphonce_are_friends->setUser2($alphonce);
        $marie_and_alphonce_are_friends->setStatus(0);
        $manager->persist($marie_and_alphonce_are_friends);

        /*****    CREATING EVENTS    *****/

        $events = array(
            array(
                "creator" => $alphonce,
                "description" => "Soirée d'anniversaire",
                "location" => "1 rue des Olivette, 44000 Nantes"
            ),
            array(
                "creator" => $alphonce,
                "description" => "Vacances Été 2021",
                "location" => "Espagne"
            ),
            array(
                "creator" => $marie,
                "description" => "Le mariage de Marie et Camille",
                "location" => "Salle des fêtes de tourcoing, 1 allée des lilas, 59200 Tourcoing, France"
            ),
        );

        $now = new \DateTime();
        for ($i = 0; $i < count($events); $i++) {
            $info_event = $events[$i];
            $event = new Event();
            $event->setCreator($info_event['creator']);
            $event->setDescription($info_event['description']);
            $event->setDate($now);
            $event->setLocation($info_event['location']);
            $manager->persist($event);

            $judith_is_invited = new Guest();
            $judith_is_invited->setUser($judith);
            $judith_is_invited->setEvent($event);
            $judith_is_invited->setStatus(false);
            $manager->persist($judith_is_invited);
        }

        $manager->flush();
    }
}
