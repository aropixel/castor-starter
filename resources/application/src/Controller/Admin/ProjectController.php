<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route("/project", name: "admin_project_")]
class ProjectController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route("/", name: "index", methods: ["GET"])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Project::class)
            ->setColumns([
                ['label' => 'Titre', 'orderBy' => 'title'],
                ['label' => 'Statut', 'orderBy' => 'status'],
                ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['title'])
            ->renderJson(fn(Project $project) => [
                $project->getTitle(),
                $project->getStatus(),
                $this->renderView('admin/project/_actions.html.twig', ['item' => $project]),
            ])
            ->render('admin/project/index.html.twig');
    }

    #[Route("/new", name: "new", methods: ["GET", "POST"])]
    public function new(Request $request): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($project);
            $this->em->flush();

            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_project_edit', ['id' => $project->getId()]);
        }

        return $this->render('admin/project/form.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/edit", name: "edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Project $project): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_project_edit', ['id' => $project->getId()]);
        }

        return $this->render('admin/project/form.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "delete", methods: ["POST", "DELETE"])]
    public function delete(Request $request, Project $project): Response
    {
        if ($this->isCsrfTokenValid('delete' . $project->getId(), $request->request->get('_token'))) {
            $this->em->remove($project);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_project_index');
    }
}
