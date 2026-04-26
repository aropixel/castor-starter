<?php

namespace App\Menu;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Aropixel\MenuBundle\Entity\MenuInterface;
use Aropixel\MenuBundle\Source\MenuSourceInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectMenuSource implements MenuSourceInterface
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly UrlGeneratorInterface $router,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getName(): string
    {
        return 'project';
    }

    public function getLabel(): string
    {
        return $this->translator->trans('menu.source.project', [], 'messages');
    }

    public function getColor(): string
    {
        return 'bg-indigo';
    }

    public function getAvailableItems(array $menuItems): array
    {
        $alreadyIncluded = $this->getAlreadyIncluded($menuItems);
        $items = [];

        foreach ($this->projectRepository->findAll() as $project) {
            $items[] = [
                'label' => $project->getTitle(),
                'value' => $project->getId(),
                'type' => 'project',
                'alreadyIncluded' => \in_array((string) $project->getId(), $alreadyIncluded, true),
            ];
        }

        return $items;
    }

    public function getSelectionTemplate(): string
    {
        return '@AropixelMenu/menu/sources/page.html.twig';
    }

    public function supports(string $type): bool
    {
        return 'project' === $type;
    }

    public function getPayload(MenuInterface $menuItem): array
    {
        return [
            'type' => 'project',
            'value' => $menuItem->getLink(),
        ];
    }

    public function mapToEntity(array $data, MenuInterface $menuItem): void
    {
        $value = $data['value'] ?? null;
        $menuItem->setLink((string) $value);

        /** @var Project|null $project */
        $project = $this->projectRepository->find($value);
        if ($project) {
            $menuItem->setTitle($project->getTitle());
        }
    }

    public function resolveUrl(MenuInterface $menuItem): string
    {
        $id = $menuItem->getLink();
        if (null === $id) {
            return '#';
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            return '#';
        }

        try {
            return $this->router->generate('app_project_show', ['slug' => $project->getSlug()]);
        } catch (RouteNotFoundException) {
            return '#';
        }
    }

    private function getAlreadyIncluded(array $menuItems): array
    {
        $included = [];
        foreach ($menuItems as $item) {
            if ($item->getLink()) {
                $included[] = $item->getLink();
            }
            if ($item->getChildren()) {
                $included = array_merge($included, $this->getAlreadyIncluded($item->getChildren()->toArray()));
            }
        }

        return array_unique($included);
    }
}
