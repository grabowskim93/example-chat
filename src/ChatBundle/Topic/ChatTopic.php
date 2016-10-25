<?php


namespace  ChatBundle\Topic;


use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use ChatBundle\Entity\Users;
use ChatBundle\Entity\Messages;

class ChatTopic implements TopicInterface
{
    protected $clients = array();
    private $entityManager;
    /**
     * @param  ConnectionInterface $connection
     * @param  Topic $topic
     * @param WampRequest $request
     */
    public function __construct($em)
    {
        $this->entityManager = $em;
        //$this->clients = new \SplObjectStorage;
    }
    
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    { 
        //sends back sessionID
        $topic->broadcast(['msg' => $connection->resourceId . " has joined ","sessionId"=>$connection->WAMP->sessionId ], array(), array($connection->WAMP->sessionId));

    }

    /**
     * @param  ConnectionInterface $connection
     * @param  Topic $topic
     * @param WampRequest $request
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        //updating status of user that left
        $users = $this->entityManager->getRepository("ChatBundle:Users")->findBy(array("socketId"=>$connection->WAMP->sessionId));
        
        if(count($users))
        {
            $this->entityManager->refresh($users[0]);
            $user = $users[0];
            
            $user->setSocketId(NULL);
            $user->setIsActive(0);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    /**
     * @param  ConnectionInterface $connection
     * @param  Topic $topic
     * @param WampRequest $request
     * @param $event
     * @param  array $exclude
     * @param  array $eligible
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {  
        $sender = $this->entityManager->getRepository("ChatBundle:Users")->findBy(array("socketId"=>$connection->WAMP->sessionId));
        if(count($sender))
            $this->entityManager->refresh($sender[0]);

        $reciver = $this->entityManager->getRepository("ChatBundle:Users")->find((int)$event["reciverId"]);
        $this->entityManager->refresh($reciver);

        if(count($sender) && $reciver)
        {
            var_dump($reciver->getId());
            var_dump($reciver->getSocketId());
            var_dump($event);
            $message = new Messages();

            $message->setMessage($event["content"]);
            $message->setReceiver($reciver);
            $message->setSender($sender[0]);
            $message->setTimestamp(time());
            $message->setMessageread(0);

            $this->entityManager->persist($message);
            $this->entityManager->flush();
            
            //message to reveiver
            $event["messageId"] = $message->getId();
            $topic->broadcast(["messagePack"=>$event], array(), array($reciver->getSocketId()));

            //callback to sender
            $topic->broadcast(["messageStatus"=>true], array(), array($connection->WAMP->sessionId));
        }
        else
            $topic->broadcast(["messageStatus"=>false], array(), array($connection->WAMP->sessionId));
       
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'acme.topic';
    }
    
}