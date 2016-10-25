<?php

namespace ChatBundle\EventListener;

use Gos\Bundle\WebSocketBundle\Event\ClientEvent;

class UserActivityListener
{
    private $entityManager;
    
    public function __construct($em) 
    {
        $this->entityManager = $em;
    }
    
    /**
     * Called whenever a client disconnects
     *
     * @param ClientEvent $event
     */
    public function onClientDisconnect(ClientEvent $event)
    {
        $connection = $event->getConnection();
        
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
        
        echo $connection->resourceId . " disconnected" . PHP_EOL;
    }
}