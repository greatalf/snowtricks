<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Category;
use App\Entity\Figure;
use App\Entity\User;
use App\Entity\Visual;
use App\Form\CategoryType;
use App\Form\CommentType;
use App\Form\FigureType;
use App\Form\VisualType;
use App\Helper\Helper;
use App\Repository\FigureRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

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
        $figures = $repo->findBy(array(), array('id' => 'DESC'), '15');

//        dd($figures);

        return $this->render('trick/home.html.twig', [
            'figures' => $figures
        ]);
    }

    /**
     * @Route("/load-more", name="load_more", methods={"GET"})
     */
    public function loadMoreAction(Request $request, FigureRepository $repository)
    {
        $limit = 15;
        $offset = $request->query->get('offset') + 15;
        $figures = $repository->findBy(array(), array('id' => 'DESC'), $limit, $offset);
        $FiguresCount = $repository->count([]);

        $tricks = [];
        foreach ($figures as $figure) {
            $tricks[] = [
                'figureId' => $figure->getId(),
                'figureThumbnail' => $figure->getThumbnail(),
                'figureTitle' => $figure->getTitle(),
            ];
        }

        return $this->json([ 'code' => 200, 'result' => ['action' => 'loadMore', 'limit' => $limit, 'offset' => $offset, 'content' => $tricks, 'totalCount' => $FiguresCount ]], 200);
    }

    /**
     * @Route("/trick/create", name="trick_create")
     * @Route("/trick/{slug}/edit", name="trick_edit")
     */
    public function form(Figure $figure = null, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$figure)
            $figure = new Figure();

        $form = $this->createForm(FigureType::class, $figure);

        $form->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() )
        {
            foreach ($figure->getVisuals() as $visual) {
                $visual->setFigure($figure);
                $manager->persist($visual);
            }

            $slugify = new Slugify();
            $figure->setSlug( $slugify->slugify( $figure->getTitle() ) );

            if(!$figure->getId())
                $figure->setCreatedAt(new \DateTime());

            $manager->persist($figure);

            $manager->flush();

            return $this->redirectToRoute('trick_show', [
                'slug' => $figure->getSlug(),
            ]);
        }

        return $this->render('trick/create.html.twig', [
            'form' => $form->createView(),
            'editMode' => $figure->getId() !== null
        ]);
    }

    /**
     * @Route("/trick/details/{slug}", name="trick_show")
     */
    public function show($slug, Request $request, EntityManagerInterface $manager, Security $security): Response
    {
        $repo = $manager->getRepository(Figure::class);
        $figure = $repo->findOneBy(['slug' => $slug]);

        /**
         * Création du formulaire de commentaire
         */
        $comment = new Comment();
        $userRepository = $manager->getRepository(User::class);
        $user = $security->getUser();
        $user = $user ?: $userRepository->find(mt_rand(1, 10));
        $form = $this->createForm(CommentType::class, $comment)->handleRequest($request);

        if( $form->isSubmitted() && $form->isValid() )
        {
            $comment->setCreatedAt(new \DateTime());
            $comment->setFigures($figure);
            $comment->setAuthor($user);

            $manager->persist($comment);

            $manager->flush();
        }

        return $this->render('trick/show.html.twig', [
            'figure' => $figure,
            'formComment' => $form->createView(),
            'editMode' => null
        ]);
    }

    /**
     * @Route("/category/create", name="category_create")
     * @Route("/category/{id}/edit", name="category_edit")
     */
    public function categoryForm(Category $category = null, Request $request, EntityManagerInterface $manager): Response
    {
        if(!$category)
            $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        $isNewCategory = !$this->helper->entityExists($category);

        if( $form->isSubmitted() && $form->isValid() )
        {
            $slugify = new Slugify();

            $manager->persist($category);

            $category->setSlug( $slugify->slugify( $category->getTitle() ) );

            $manager->flush();

            $categoryFlashMsg = 'La catégorie a bien été ';
            $categoryFlashMsg .= $isNewCategory ? 'crée' : 'modifiée';

            if($this->helper->entityExists($category))
                $this->addFlash('success', $categoryFlashMsg);

            return $this->redirect($this->generateUrl('home') . '#msg_flash');

        }

        return $this->render('category/create.html.twig', [
            'formCategory' => $form->createView(),
            'editMode' => $isNewCategory
        ]);
    }

    /**
     * @Route("/trick/{id}/ajaxdelete", name="trick_ajax_delete")
     */
    public function ajaxDelete($id, EntityManagerInterface $manager, Request $request): Response
    {
        $figure = $this->registry->getRepository(Figure::class)->find($id);
        $visuals = $figure->getVisuals();

        // D'abord supprimer les visuals de la figure avant la figure même
        foreach ($visuals as $visual) {
            $manager->remove($visual);
        }

        $manager->remove($figure);
        $manager->flush();

        return $this->json(['code' => 200, 'result' => ['action' => 'delete', 'figure_id' => $id ], 'message' => ['messageType' => 'success', 'messageText' => "La figure a bien été effacée"]], 200);
    }

    /**
     * @Route("/trick/{id}/delete", name="trick_delete")
     */
    public function delete($id, EntityManagerInterface $manager, Request $request): Response
    {
        $figure = $this->registry->getRepository(Figure::class)->find($id);
        $visuals = $figure->getVisuals();

        // D'abord supprimer les visuals de la figure avant la figure même
        foreach ($visuals as $visual) {
            $manager->remove($visual);
        }

        $manager->remove($figure);
        $manager->flush();

        return $this->helper->redirectWithFlash('home', 'success', 'La figure a bien été supprimée');
    }


}
