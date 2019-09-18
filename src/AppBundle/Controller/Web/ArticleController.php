<?php

namespace AppBundle\Controller\Web;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
use AppBundle\Service\ArticleService;
use AppBundle\Service\FileUploader;
use AppBundle\Service\RedisClientCreator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{

    /**
     * @Route("/addArticle", name="article_add")
     */
    public function addArticle(Request $request, ArticleService $articleService, FileUploader $fileUploader)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        $currentUser = $this->getUser();

        if ( $form->isSubmitted() && $form->isValid() ) {

            $article   = $articleService->addArticleToDatabase($article, $fileUploader, $currentUser);

            $followers = $articleService->deliverArticleToFollowers($currentUser->getId(), $article->getId());

            $articleService->addArticleToRedis($article, $followers);

            $status = [
                'status' => 'success'
            ];

            return new JsonResponse($status);
        }

        return $this->render('articles/add_edit_article.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/editArticle/{id}", name="edit_article", methods={"POST", "GET"})
     */
    public function editArticle(Request $request, $id, FileUploader $fileUploader)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var Article $article */
        $article = $this->getDoctrine()->getRepository(Article::class)->find($id);
        $roles = $this->getUser()->getRoles();
        if ( !in_array('ROLE_ADMIN', $roles) && $this->getUser()->getId() != $article->getAuthor()->getId() ) {
            throw new AccessDeniedHttpException('You don`t have access here!!!');
        }
        $oldImageName = $article->getImage();
        $file = new UploadedFile('uploads/articleImages/' . $article->getImage(), $article->getImage());
        $article->setImage($file);
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            if ( $article->getImage() != null ) {

                unlink('D:\\untitled\\web\\uploads\\articleImages\\' . $oldImageName);
                /** @var  $newImage */
                $newImage = $article->getImage();

                $newImageName = $fileUploader->uploadImage($newImage, $this->getParameter('articles_directory'));

                $article->setImage($newImageName);
            }else {
                $article->setImage($oldImageName);
            }
            $article->setAuthorId($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $status = [
              'status' => 'success'
            ];

            return new JsonResponse($status);
        }

        return $this->render('articles/edit_article.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
