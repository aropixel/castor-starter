<?php

namespace App\DataFixtures;

use Aropixel\AdminBundle\Entity\Publishable;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProjectFixture extends Fixture
{
    private const PROJECTS = [
        ['title' => 'Refonte site e-commerce', 'description' => 'Refonte complète d\'une boutique en ligne sur Symfony avec intégration de paiement.'],
        ['title' => 'Application mobile RH', 'description' => 'Développement d\'une application mobile de gestion des ressources humaines.'],
        ['title' => 'Portail client B2B', 'description' => 'Création d\'un portail client B2B avec espace collaboratif et gestion de projets.'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PROJECTS as $i => $data) {
            $project = new Project();
            $project->setTitle($data['title']);
            $project->setDescription($data['description']);
            $project->setStatus(Publishable::STATUS_ONLINE);

            $manager->persist($project);
            $this->addReference('project-' . ($i + 1), $project);
        }

        $manager->flush();
    }
}
