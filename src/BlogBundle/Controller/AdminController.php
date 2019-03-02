<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\Message;
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


/**
 * @Route("admin")
 * Class AdminController
 * @package BlogBundle\Controller
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_index")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewUsers() {
        $currentUser = $this->getUser();
        if (!$currentUser->isAdmin() )
        {
            return $this->redirectToRoute("blog_index");
        }
        /** @var User[] $users */
        $users = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('admin/index.html.twig',
            ['users' => $users]);
    }

    /**
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/user/profile/{id}", name="admin_user_profile")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
   public function userProfile($id)
   {
       $currentUser = $this->getUser();
       if (!$currentUser->isAdmin() )
       {
           return $this->redirectToRoute("blog_index");
       }

       /** @var User $user */
       $user=$this->getDoctrine()
           ->getRepository(User::class)
           ->find($id);
       return $this->render("admin/user/profile.html.twig",
           ['user' =>$user]);
   }

    /**
     * @Route("/user/articles/{id}", name="user_articles")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewUserArticles($id, Request $request) {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(['id' => $id]);

        /** @var Article[] $articles */
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(['author' => $user]);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $articles, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            3/*limit per page*/
        );

        return $this->render('article/myArticles.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @Route("/user/comments/{id}", name="user_comments")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewUserComments($id, Request $request) {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(['id' => $id]);

        /** @var Comment[] $comments */
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['author' => $user]);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $comments, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            4/*limit per page*/
        );
        return $this->render('comment/userComments.html.twig', ['pagination' =>$pagination]);
    }

    /**
     * @Route("/user/mailbox{id}", name="admin_user_mailbox")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailBox($id) {
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        /** @var Message[] $inMessages */
        $inMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['recipient' => $user], ['dateAdded' => 'desc']);
        /** @var Message[] $outMessages */
        $outMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['sender' => $user], ['dateAdded' => 'desc']);

        return $this->render('/admin/user/mailbox.html.twig',
            ['inMessages'=>$inMessages, 'outMessages' => $outMessages]);
    }
}
