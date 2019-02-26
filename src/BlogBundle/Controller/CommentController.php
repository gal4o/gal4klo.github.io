<?php
namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\User;
use BlogBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends Controller
{
    /**
     * @Route("/article/{id}/comment", name="add_comment")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param Article $article
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addComment(Request $request, Article $article)
    {
        $user = $this->getUser();

        $author = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($user->getId());
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted()&& $form->isValid()){
            $comment->setAuthor($author);
            $comment->setArticle($article);
            $author->addComment($comment);
            $article->addComment($comment);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            $this->addFlash('info', "Comment is created successfully!");

            return $this->redirectToRoute('article_view',
                ['id' => $article->getId()]);
        }

        return $this->render('comment/create.html.twig',
            ['form' =>$form->createView(),
                'article' => $article]);
    }

    /**
     * @Route("/comment/edit/{id}", name="comment_edit")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editComment($id, Request $request)
    {
        /** @var Comment $comment */
        $comment = $this-> getDoctrine()
            ->getRepository(Comment::class)
            ->find($id);
        $currentUser = $this->getUser();
        if (!$currentUser->isAuthor($comment)&&!$currentUser->isAdmin() )
        {
            return $this->redirectToRoute('blog_index');
        }
        /** @var Article $article */
        $article = $comment->getArticle();
        if ($comment === null||$article === null) {
            return $this->redirectToRoute('blog_index');
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted()&& $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            $this->addFlash('info', "Comment is edited successfully!");

            return $this->redirectToRoute('article_view',
                ['id' => $article->getId()]);
        }
        return $this->render('comment/edit.html.twig',
            ['form' =>$form->createView(),
                'comment' => $comment,
                'article' => $article]);
    }

    /**
     * @Route("/comment/delete/{id}", name="comment_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteComment($id, Request $request)
    {
        /** @var Comment $comment */
        $comment = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->find($id);
        $currentUser = $this->getUser();
        if (!$currentUser->isAuthor($comment)&&!$currentUser->isAdmin() )
        {
            return $this->redirectToRoute('blog_index');
        }
        /** @var Article $article */
        $article = $comment->getArticle();
        if ($comment === null) {
            return $this->redirectToRoute('blog_index');
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted()&&$form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();
            $this->addFlash('info', "Comment is deleted successfully!");

            return $this->redirectToRoute('article_view',
                ['article' => $article]);
        }
        return $this->render('comment/delete.html.twig',
            array('article' => $article,
                'comment' => $comment,
                'form' => $form->createView()));
    }
}
