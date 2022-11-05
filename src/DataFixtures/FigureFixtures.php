<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Figure;
use App\Entity\User;
use App\Entity\Visual;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class FigureFixtures extends Fixture
{
    protected $slugger;
    protected $encoder;

    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $encoder)
    {
        $this->slugger = $slugger;
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
////        for ($i=1; $i <= 15; $i++)
////        {
////            $user = new User();
////            $user->setEmail("laurent@snowtricks.com")
////                ->setPassword()
////                ->setUsername("greatalf")
////                ->setFirstName("Laurent")
////                ->setLastName("AVRIL")
////                ->setAvatar()
////                ->setConfirmed()
////                ->setPassword()
////                ->setToken()
////
////            $manager->persist($figure);
////        }
////
////        for ($i=1; $i <= 10; $i++)
////        {
////            $figure = new Figure();
////            $figure->setTitle("Titre figure n° $i")
////                    ->setContent("Description de figure n° $i")
////                    ->setThumbnail("http://placehold.it/350x150")
////                    ->setCreatedAt(new \DateTimeImmutable());
////
////            $manager->persist($figure);
////        }
////
////        $manager->flush();
//
//
//
        $faker = \Faker\Factory::create('fr_FR');

        //Gestion des users
        $users = [];
        $genres = ['male', 'female'];

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $genre = $faker->randomElement($genres);

            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';

            $picture .= ($genre == "male" ? 'men/' : 'women/') . $pictureId;

            $password = $this->encoder->encodePassword($user,'0000');

            $user->setFirstName($faker->firstname)
                ->setLastName($faker->lastname)
                ->setEmail($faker->email)
                ->setUsername($faker->username)
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->setPassword($password)
                ->setConfirmed(1)
                ->setToken( md5(uniqid()) )
                ->setavatar( $picture );

            $manager->persist($user);
            $users[] = $user;

        }

        // 4 fake categories
        for ($i = 1; $i <= 6; $i++) {
            $category = new Category();
            $category->setTitle($faker->word())
                ->setDescription($faker->paragraph());

            $manager->persist($category);

            // between 6 & 8 fake figures
            for ($j = 1; $j <= mt_rand(6, 8); $j++) {
                $content = '<p>';
                $content .= join($faker->paragraphs(3), '</p><p>');
                $content .= '</p>';

                $nb = $faker->numberBetween($min = 1, $max = 3);
                $title = $faker->sentence($nbWords = $nb, $variableNbWords = true);
                $title = str_replace('.', '', $title);

                $slug = str_replace(' ', '-', $title);
                $slug = str_replace('\'', '-', $slug);

                $figure = new Figure();

                $user = $users[mt_rand(0, count($users) - 1)];

                $figure->setTitle($title)
                    ->setContent($content)
                    ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                    ->setCategory($category)
                    ->setSlug($slug)
                    ->setThumbnail($faker->imageUrl())
                    ->setAuthor($user);

                $manager->persist($figure);

                //Visuals of figure
                for ($m = 1; $m <= mt_rand(20, 50); $m++) {
                    $visual = new Visual();

                    $visual->setUrl($faker->imageUrl())
                        ->setCaption($faker->sentence())
                        ->setFigure($figure);

                    $manager->persist($visual);
                }
                //Comments of figure
                for ($k = 1; $k <= mt_rand(12, 20); $k++) {
                    $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';
                    $now = new \DateTime();
                    $interval = $now->diff($figure->getCreatedAt());
                    $days = $interval->days;
                    $minimum = '-' . $days . ' days';

                    $user = $users[mt_rand(0, count($users) - 1)];

                    $comment = new Comment();
                    $comment->setAuthor($user)
                        ->setContent($content)
                        ->setCreatedAt($faker->dateTimeBetween($minimum))
                        ->setFigures($figure);

                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }
}
