<?php

namespace ChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ChatBundle\Entity\Users;
use ChatBundle\Entity\Messages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class DefaultController extends Controller
{
    private $entityManager;
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        //authentication
        $securityContext = $this->container->get('security.authorization_checker');
        if (!$securityContext->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->redirect($this->generateUrl("login"));
        
        //getting active users
        $this->entityManager = $this->getDoctrine()->getManager();
        $users  = $this->entityManager->getRepository("ChatBundle:Users")->findBy(array("isActive"=>1));
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id = $user->getId();
        
        return array('users'=> $this->parseUserObject($users, $user),"user"=>$user);
    }

    /**
     * @Route("/homepage")
     */
    public function homePageAction(){
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime('2 minutes ago'));
        $repository = $this->getDoctrine()->getRepository('ChatBundle:Users');
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT u FROM ChatBundle:Users u WHERE u.lastActivityAt > :delay'
                        )->setParameter('delay', $delay);
            $usersAuth = $query->getResult();

        }

        return $this->render('ChatBundle:Default:index.html.twig', array('users'=>$usersAuth));
        /*return array(
            'users'=> $usersAuth
        );*/

//        return $this->render('ChatBundle:Default:index.html.twig');
    }

    /**
     * @Route("/chat")
     */
    public function chatAction(){
        $delay = new \DateTime();
        $delay->setTimestamp(strtotime('2 minutes ago'));
        $repository = $this->getDoctrine()->getRepository('ChatBundle:Users');
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery(
                'SELECT u FROM ChatBundle:Users u WHERE u.lastActivityAt > :delay'
            )->setParameter('delay', $delay);
            $usersAuth = $query->getResult();

        }

        return $this->render('ChatBundle:Default:chat.html.twig', array('users'=>$usersAuth));
    }

    /**
     * @Route("/ajax", name="_recherche_ajax")
     */
    public function ajaxAction(Request $request)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        if($request->isXmlHttpRequest())
        {
            $mode = $request->request->get('mode');
           // $mode = "getConversation";
            return new JsonResponse($this->$mode($request));
        }
        return new Response("Acces Denied");
    }
    
    private function connectSocket($request)
    {
        $id = $request->request->get("id");
        $socketId = $request->request->get("socketId");
        
        if(empty($id) || empty($socketId))
            return array("status"=>false);
        
        $user = $this->entityManager->getRepository("ChatBundle:Users")->find($id);
        if(!$user )
            return array("status"=>false);
        
        $user->setSocketId($socketId);
        $user->setIsActive(1);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return array("status"=>true);
    }
    
    private function getActiveUsers($request)
    {
        $id = $request->request->get("id");
        
        if(empty($id))
            return array("status"=>false);
        
        $user = $this->entityManager->getRepository("ChatBundle:Users")->find($id);
        if(!$user )
            return array("status"=>false);
        
        $users = $this->entityManager->getRepository("ChatBundle:Users")->findBy(array("isActive"=>1));
        
        return  array("users"=>$this->parseUserObject($users,$user));
    }
    private function getConversation($request)
    {
        $id = $request->request->get("id");
        $receiver = $request->request->get("receiver");
        
        $limit = $request->request->get("limit");
       
        if(empty($id) || empty($receiver))
            return array("status"=>false);
        
        $limit = ($limit)? $limit : 5;
        $mesages = $this->entityManager->getRepository("ChatBundle:Messages")->createQueryBuilder("m")
                ->select("m")
                ->where("m.sender = :sender AND m.receiver = :receiver")
                ->orWhere("m.sender = :receiver AND m.receiver = :sender")
                ->setParameter("sender",$id)
                ->setParameter("receiver",$receiver)
                ->orderBy("m.timestamp","DESC")
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        
        $mesagesAll = $this->entityManager->getRepository("ChatBundle:Messages")->createQueryBuilder("m")
                ->select("m")
                ->where("m.sender = :sender AND m.receiver = :receiver")
                ->orWhere("m.sender = :receiver AND m.receiver = :sender")
                ->setParameter("sender",$id)
                ->setParameter("receiver",$receiver)
                ->orderBy("m.timestamp","DESC")
                ->getQuery()
                ->getResult();
        $allGiven = (count($mesages) == count($mesagesAll))? true : false;
        $parsed = $this->parseMessages($mesages);
        
        //setting receiver messages as read
        foreach($mesages as $message)
        {
            if($message->getReceiver()->getId() == $id)
            {
                $message->setMessageread(1);
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            }
        }
        
        return array("messages"=>$parsed,"allGiven"=>$allGiven);
    }
    private function tagMessageAsRead($request)
    {
        $messageId = $request->request->get("messageId");
        
        if(empty($messageId))
            return array("status"=>false);
        
        $message = $this->entityManager->getRepository("ChatBundle:Messages")->find($messageId);
        
        if(!$message)
            return array("status"=>false);
        
        $message->setMessageread(1);
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        
        return array("status"=>true);
    }
    private function parseMessages($mesages)
    {
        $messagesArray = array();
        foreach($mesages as $message)
        {
            $mes["id"] = $message->getId();
            $mes["message"] = $message->getMessage();
            $mes["sender"] = $message->getSender()->getId();
            $mes["receiver"] = $message->getReceiver()->getId();
            
            $messagesArray[] = $mes;
        }
        return $messagesArray;
    }
    private function parseUserObject($users, $currentUser)
    {
        $newUsersArray = array();
        
        foreach($users as $user)
        {
            $unread = $this->entityManager->getRepository("ChatBundle:Messages")->findBy(array("messageread"=>0,"receiver"=>$currentUser,"sender"=>$user));
            
            $newUser["id"] = $user->getId();
            $newUser["socketId"] = $user->getSocketId();
            $newUser["username"] = $user->getUsername();
            $newUser["unreadMessages"] = count($unread);
            
            $newUsersArray[] = $newUser;
        }
        return $newUsersArray;
    }
}

