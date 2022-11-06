<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Figure;
use App\Form\CategoryType;
use App\Form\FigureType;
use App\Helper\Helper;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    protected $registry;
    protected $helper;

    public function __construct(ManagerRegistry $registry, Helper $helper)
    {
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * @Route("/trick", name="app_trick")
     */
    public function index(): Response
    {
        return $this->render('trick/index.html.twig', [
            'controller_name' => 'TrickController',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        $repo = $this->registry->getRepository(Figure::class);
//        $figures = $repo->findAll();
        $figures = $repo->findBy(array(), array('id' => 'DESC'));

        return $this->render('trick/home.html.twig', [
            'figures' => $figures
        ]);
    }

    /**
     * @Route("/trick/create", name="trick_create")
     * @Route("/trick/{id}/edit", name="trick_edit")
     */
    public function form(Figure $figure = null, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$figure)
            $figure = new Figure();

        $form = $this->createForm(FigureType::class, $figure);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() )
        {
            $slugify = new Slugify();
            $figure->setSlug( $slugify->slugify( $figure->getTitle() ) );

            if(!$figure->getId())
                $figure->setCreatedAt(new \DateTime());

            $manager->persist($figure);
            $manager->flush();

            return $this->redirectToRoute('trick_show', [
                'id' => $figure->getId(),
            ]);
        }

        return $this->render('trick/create.html.twig', [
            'formFigure' => $form->createView(),
            'editMode' => $figure->getId() !== null
        ]);
    }

    /**
     * @Route("/trick/details/{id}", name="trick_show")
     */
    public function show($id): Response
    {
        $repo = $this->registry->getRepository(Figure::class);
        $figure = $repo->find($id);

        return $this->render('trick/show.html.twig', [
            'figure' => $figure
        ]);
    }

    /**
     * @Route("/category/create", name="category_create")
     * @Route("/category/{id}/edit", name="category_edit")
     */
    public function categoryForm(Category $category = null, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);

        if(!$category)
            $category = new Category();

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() )
        {
            $slugify = new Slugify();
            $category->setSlug( $slugify->slugify( $category->getTitle() ) );

            $manager->persist($category);
            $manager->flush();

            if($this->helper->entityExists($category))
                $this->addFlash('success', 'La catégorie a bien été modifiée');

            return $this->redirect($this->generateUrl('home') . '#msg_flash');

        }

        return $this->render('category/create.html.twig', [
            'formCategory' => $form->createView(),
            'editMode' => $this->helper->entityExists($category)
        ]);
    }

    /**
     * @Route("/trick/{id}/delete", name="trick_delete")
     */
    public function delete($id, EntityManagerInterface $manager): Response
    {
        $figure = $this->registry->getRepository(Figure::class)->find($id);
        $visuals = $figure->getVisuals();

        // D'abord supprimer les visuals de la figure avant la figure même
        foreach ($visuals as $visual) {
            $manager->remove($visual);
        }

        $manager->remove($figure);
        $manager->flush();

        return $this->helper->redirectWithFlash('home', 'success', 'La figure a bien été effacée');
    }


}
