<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Role;
use BlogBundle\Entity\User;
use BlogBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request){
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()) { //&&$form->isValid()
            $emailForm = $form->getData()->getEmail();
            $userForm = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $emailForm]);

            if ($userForm !== null) {
                $this->addFlash('info', "Username with email ". $emailForm . "already taken!");
                return $this->render('user/register.html.twig', ['form' => $form->createView()]);
            }
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            
            $user->setPassword($password);

            $roleRepository = $this->getDoctrine()->getRepository(Role::class);
            $userRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);

            $user->setRoles($userRole);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("security_login");
        }
        return $this->render('user/register.html.twig',
            ['form' =>$form->createView()]);
    }

    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile(){
        $id = $this->getUser()->getId();
        $user=$this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);
        return $this->render("user/profile.html.twig",
            ['user' =>$user]);
    }
}
