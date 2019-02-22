<?php
namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\Comment;
use BlogBundle\Entity\User;
use BlogBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends Controller
{
    /**
     * @Route("/article/{id}/comment", name="add_comment")
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
//            /** Article @article */
            $article->addComment($comment);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('article_view',
                ['id' => $article->getId()]);
        }
        return $this->render('comment/create.html.twig',
            ['form' =>$form->createView(),
                'article' => $article]);
    }
}
