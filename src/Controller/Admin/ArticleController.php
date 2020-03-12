<?php


namespace App\Controller\Admin;


use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller\Admin
 *
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(ArticleRepository $repository)
    {
        $articles = $repository->findBy([], ['publicationDate' => 'DESC']);

        return $this->render(
            'admin/article/index.html.twig',
            ['articles' => $articles]
        );
    }

    /**
     * @Route("/edition")
     */
    public function edit(Request $request, EntityManagerInterface $manager)
    {
        /*
        * Intégrer le formulaire pour l'enregistrement d'un article
        * Validation : tous les champs obligatoires
        * Avant l'enregistrement setter la date de publication à maintenant
        * et l'auteur avec l'utilisateur connecté ($this->getUser() dans un contrôleur)
        *
        * Adapter la page pour la modification :
        * - pas de modification de la date de publication ni de l'auteur
        */
        $article = new Article();
        $article->setAuthor($this->getUser());

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted()){
            if ($form->isValid()){
                $manager->persist($article);
                $manager->flush();

                $this->addFlash('success', "L'article est enregistré");

                return $this->redirectToRoute('app_admin_article_index');

            }else{
                $this->addFlash(
                    'danger',
                    'Le formulaire contient des erreurs'
                );

            }
        }
        return $this->render(
            'admin/article/edit.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }








}
