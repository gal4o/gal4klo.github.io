<?php

namespace BlogBundle\Controller;

use BlogBundle\Entity\Message;
use BlogBundle\Entity\User;
use BlogBundle\Form\MessageType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends Controller
{
    /**
     * @Route("user/{id}/message", name="user_message")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addMessage(Request $request, $id)
    {
        $currentUser = $this->getUser();
        $recipient = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);
        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            $message->setSender($currentUser)
                ->setRecipient($recipient)
                ->setIsReader(false);
            $em = $this->getDoctrine()
                ->getManager();
            $em->persist($message);
            $em->flush();

            $this->addFlash("info", "Message sent successfully!");
            return $this->redirectToRoute('user_mailbox');
        }

        return $this->render('user/send_message.html.twig',
            ['form' => $form->createView(), 'user' =>$recipient->getFullName()]
        );
    }

    /**
     * @Route("user/mailbox", name="user_mailbox")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function mailBox() {
        /** @var Message[] $inMessages */
        $inMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['recipient' =>$this->getUser()], ['dateAdded' => 'desc']);
        /** @var Message[] $outMessages */
        $outMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['sender' =>$this->getUser()], ['dateAdded' => 'desc']);

        return $this->render('user/mailbox.html.twig',
            ['inMessages'=>$inMessages, 'outMessages' => $outMessages]);
    }

    /**
     * @Route("user/mail/{id}", name="user_mail")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewMessage($id, Request $request) {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        /** @var Message $message */
        $message = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->find(['id' => $id]);
        if($currentUser->isAdmin()||($message->getRecipient()===$currentUser||$message->getSender()===$currentUser)){
            if ($currentUser===$message->getRecipient()) {
                $message->setIsReader(true);
                $em = $this->getDoctrine()
                    ->getManager();
                $em->persist($message);
                $em->flush();
            }
            $sendMessage = new Message();
            $form = $this->createForm(MessageType::class, $sendMessage);
            $form->handleRequest($request);
            if($form->isSubmitted()){
                $sendMessage
                    ->setSender($this->getUser())
                    ->setRecipient($message->getSender())
                    ->setIsReader(false);
                $em = $this->getDoctrine()
                    ->getManager();
                $em->persist($sendMessage);
                $em->flush();

                $this->addFlash("message", "Message sent successfully!");

                return $this->redirectToRoute("user_mail", ['id' => $id]);
            }
                return $this->render('user/message.html.twig',
                    ['message' => $message, 'form' => $form->createView()]);

        }
        $this->addFlash("info", "Access denied!");
        return $this->redirectToRoute('blog_index');
    }
}
