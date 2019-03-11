<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\User;
use BlogBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ArticleController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/article/create", name="article_create")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse| \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()&&$form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();

            if ($file != null) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('article_directory'),
                        $fileName);
                    $article->setImage($fileName);

                } catch (FileException $ex) {
                    echo "An error occurred while creating your photo";
                }
            }

            $article->setAuthor($this->getUser());
            $article->setViewsCount('0');
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('info', "Article is created successfully!");

            return $this->redirectToRoute("blog_index");
        }

        return $this->render('article/create.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @Route("/article/{id}", name="article_view")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewArticle($id)
    {
        /** @var Article $article */
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);

        $article->setViewsCount($article->getViewsCount()+1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $this->render('article/details.html.twig',
            ['article' => $article]);
    }

    /**
     * @Route("/myArticles", name="myArticles")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewMyArticles(Request $request) {
        /** @var Article[] $articles */
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(['author' => $this->getUser()]);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $articles, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            3/*limit per page*/
        );

        return $this->render('article/myArticles.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @Route("/article/edit/{id}", name="article_edit")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editArticle($id, Request $request)
    {
        /** @var Article $article */
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);
        if ($article === null) {
            return $this->redirectToRoute("blog_index");
        }
        $currentUser = $this->getUser();
        if (!$currentUser->isAuthor($article)&&!$currentUser->isAdmin() )
        {
            return $this->redirectToRoute("blog_index");
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()&&$form->isValid())
        {
            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
//
//            if ( $article->getImage()!= null) {
//                var_dump($this->getParameter('article_directory'.'/'.$article->getImage()));exit();
//                $fs = new Filesystem();
//                $fs->remove([$this->getParameter('article_directory'.'/')]);
//            }

            if ($file != null) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('article_directory'),
                        $fileName);
                    $article->setImage($fileName);
                } catch (FileException $ex) {
                    echo "An error occurred while editing your photo";
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('info', "Article is edited successfully!");

            return $this->redirectToRoute('article_view',
                array('id' => $article->getId()));
        }
        return $this->render('article/edit.html.twig',
            array('article' => $article,
                'form' => $form->createView()));
    }

    /**
     * @Route("/article/delete/{id}", name="article_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id, Request $request)
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);
        if ($article === null) {
            return $this->redirectToRoute('blog_index');
        }
        $currentUser = $this->getUser();

        if (!$currentUser->isAuthor($article)&&!$currentUser->isAdmin() )
        {
            return $this->redirectToRoute('blog_index');
        }
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted()&&$form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();
            $this->addFlash('info', "Article is deleted successfully!");

            return $this->redirectToRoute('blog_index');
        }
        return $this->render('article/delete.html.twig',
            array('article' => $article,
                'form' => $form->createView()));
    }

    /**
     * @Route("/article/like/{id}", name="article_likes")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function likes($id) {
        /** @var User $user */
        $user = $this->getUser();
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);
        if ($user->isLiker($article)) {
            $user->removeLikes($article);
            $article->removeLikers($user);
            $this->addFlash("info", "You are disliked this article!");
        } else{
            $article->setLikers($user);
            $user->addLikes($article);
            $this->addFlash("info", "You are liked this article!");

        }

        $em = $this->getDoctrine()
            ->getManager();
//        $em->persist($article);
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('article_view',
            ['id' => $id]);
    }
}
