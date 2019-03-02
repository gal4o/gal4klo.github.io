<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="blog_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var Article[] $articles */
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy([], ['dateAdded' => 'desc', 'viewsCount' => 'desc']);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $articles, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            4/*limit per page*/
        );
        return $this->render('default/index.html.twig', ['pagination' => $pagination]);
    }
}
