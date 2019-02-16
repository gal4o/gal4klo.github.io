<?php

namespace BlogRestApiBundle\Controller;

use BlogBundle\Entity\Article;
use BlogBundle\Entity\User;
use BlogBundle\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends Controller
{
    /**
     * @Route("/articles", methods={"GET"}, name="rest_api_articles")
     */
    public function articlesAction()
    {
        $articles = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findAll();
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($articles, 'json');

        return new Response($json,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

    /**
     * @Route("/articles/{id}",methods={"GET"}, name="rest_api_article")
     * @param $id article id
     * @return JsonResponse
     */
    public function articleAction($id)
    {
        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->find($id);
        if ($article === null) {
            return new JsonResponse(array('error' =>
                'resource not found'),
                Response::HTTP_NOT_FOUND,
                array('content-type' => 'application/json')
            );
        }
        $serializer = $this->container->get('jms_serializer');
        $articleJson = $serializer->serialize($article, 'json');

        return new JsonResponse($articleJson,
            Response::HTTP_OK,
            array('content-type' => 'application/json')
        );
    }

//    /**
//     * @Route("/articles/create",methods={"POST"} name="rest_api_article_create")
//     * @param Request $request
//     * @return Response
//     */
//    public function createAction(Request $request)
//    {
//        try {
//            $this->createNewArticle($request);
//            return new Response(null, Response::HTTP_CREATED);
//        } catch (\Exception $e) {
//            return new JsonResponse(['error' => $e->getMessage()],
//                Response::HTTP_BAD_REQUEST,
//                array('content-type' => 'application/json')
//            );
//        }
//    }

    /**
     *
     * @param Request $request
     * @return Article
     * @throws \Exception
     */
    public function createNewArticle(Request $request)
    {
        $article = new Article();
        $parameters = $request->request->all();
        $persistedType = $this->processForm($article, $parameters, 'POST');
        return $persistedType;
    }

    /**
     * @param $article
     * @param $params
     * @param string $method
     * @return Article
     * @throws \Exception
     */
    public function processForm(Article $article, $params, $method = 'PUT')
    {
        foreach ($params as $param => $paramValue) {
            if ($paramValue === null || strlen(trim($paramValue)) === 0) {
                throw new \Exception("invalid data: $param");
            }
        }
        if (!array_key_exists('authorId', $params)) {
            throw new \Exception('invalid data: authorId');
        }
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($params['authorId']);
        if ($user === null) {
            throw new \Exception('invalid data: user id');
        }
        $form = $this->createForm
        (ArticleType::class, $article, ['method' => $method]);
        $form->submit($params);
        if ($form->isSubmitted()) {
            $article->setAuthor($user->getId());
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            return $article;
        }
        throw new \Exception('submitted data is invalid');
    }

    /**
     * @Route("/articles/{id}",methods={"PUT"}, name="rest_api_article_edit")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        try {
            $article = $this->getDoctrine()
                ->getRepository(Article::class)
                ->find($id);
            if ($article === null) { //create new
                $this->createNewArticle($request);
                $statusCode = Response::HTTP_CREATED;
            } else { //update existing
                $this->processForm($article, $request->request->all(), 'PUT');
                $statusCode = Response::HTTP_NO_CONTENT;
            }
            return new Response(null, $statusCode);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST,
                array('content-type' => 'application_json')
            );
        }
    }

//    /**
//     * @Route("/articles/{id}",methods={"DELETE"} name="rest_api_article_edit")
//     * @param $id
//     * @return Response
//     */
//    public function deleteAction($id)
//    {
//        try {
//            $article = $this->getDoctrine()
//                ->getRepository(Article::class)
//                ->find($id);
//            if ($article === null) {
//                $statusCode = Response::HTTP_NOT_FOUND;
//            } else {
//                $em = $this->getDoctrine()->getManager();
//                $em->remove($article);
//                $em->flush();
//
//                $statusCode = Response::HTTP_NO_CONTENT;
//            }
//            return new Response(null, $statusCode);
//        } catch (\Exception $e) {
//            return new JsonResponse(['error' => $e->getMessage()],
//                Response::HTTP_BAD_REQUEST,
//                array('content-type' => 'application_json')
//            );
//        }
//    }
}
