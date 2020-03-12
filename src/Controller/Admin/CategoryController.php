<?php


namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 *
 * @Route("/categorie")
 */
class CategoryController extends AbstractController
{
    /**
     * Nom de cette route : app_admin_category_index
     * @Route("/")
     */
    public function index(CategoryRepository $repository)
    {
        $categories = $repository->findBy([], ['id' => 'ASC']);

        return $this->render(
            'admin/category/index.html.twig',
            ['categories' => $categories]
        );
    }

    /**
     * @Route("/edition/{id}", defaults={"id": null}, requirements={"id": "\d+"})
     */
    public function edit(Request $request, EntityManagerInterface $manager, $id)
    {
        if (is_null($id)) { // création
            $category = new Category();
        } else { // modification
            // équivaut à un find($id) par CategoryRepository
            $category = $manager->find(Category::class, $id);

            dump($category);

            // 404 si l'id n'existe pas en bdd
            if (is_null($category)) {
                throw new NotFoundHttpException();
            }
        }

        // création du formulaire relié à la catégorie
        $form = $this->createForm(CategoryType::class, $category);

        // le formulaire analyse la requête
        // et fait le lien avec l'entité Category s'il a été soumis
        $form->handleRequest($request);

        dump($category);

        // si le formulaire a été soumis
        if ($form->isSubmitted()) {
            // si les validations à partir des annotations
            // dans l'entité Category sont ok
            if ($form->isValid()) {
                // enregistrement en bdd par l'entity manager
                $manager->persist($category);
                $manager->flush();

                // enregistre un message dans la session pour affichage unique
                $this->addFlash(
                    'success',
                    'La catégorie "' . $category->getName() . '" est enregistrée'
                );

                // redirection vers la liste
                return $this->redirectToRoute('app_admin_category_index');
            } else {
                $this->addFlash(
                    'error',
                    'Le formulaire contient des erreurs'
                );
            }
        }

        return $this->render(
            'admin/category/edit.html.twig',
            [
                // passage du formulaire au template
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Paramconverter : le paramètre typé Category contient la catégorie
     * dont l'id est passé dans la partie variable de l'url
     *
     * @Route("/suppression/{id}", requirements={"id": "\d+"})
     */
    public function delete(EntityManagerInterface $manager, Category $category)
    {
        if(!$category->getArticles()->isEmpty()){
            $this->addFlash('warning', "La catégorie contient des articles elle ne peut pas etre supprimée");
        } else{
            // suppression en bdd
            $manager->remove($category);
            $manager->flush();

            $this->addFlash('success', 'La catégorie est supprimée');
        }



        return $this->redirectToRoute('app_admin_category_index');
    }
}
