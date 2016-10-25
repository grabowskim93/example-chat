<?php
/**
 * Created by PhpStorm.
 * User: mateuszgra
 * Date: 2016-06-08
 * Time: 23:52
 */

namespace ChatBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="messages")
 */
class Messages
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $senderId;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $receiverId;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $timestamp;

    /**
     * @ORM\Column(type="string")
     */
    private $message;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSenderId()
    {
        return $this->sendedId;
    }

    /**
     * @param mixed $sendedId
     */
    public function setSenderId($sendedId)
    {
        $this->sendedId = $sendedId;
    }

    /**
     * @return mixed
     */
    public function getReceiverId()
    {
        return $this->receiverId;
    }

    /**
     * @param mixed $receiverId
     */
    public function setReceiverId($receiverId)
    {
        $this->receiverId = $receiverId;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @var \ChatBundle\Entity\Users
     */
    private $receiver;

    /**
     * @var \ChatBundle\Entity\Users
     */
    private $sender;


    /**
     * Set receiver
     *
     * @param \ChatBundle\Entity\Users $receiver
     *
     * @return Messages
     */
    public function setReceiver(\ChatBundle\Entity\Users $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get receiver
     *
     * @return \ChatBundle\Entity\Users
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set sender
     *
     * @param \ChatBundle\Entity\Users $sender
     *
     * @return Messages
     */
    public function setSender(\ChatBundle\Entity\Users $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \ChatBundle\Entity\Users
     */
    public function getSender()
    {
        return $this->sender;
    }
    /**
     * @var boolean
     */
    private $messageread;


    /**
     * Set messageread
     *
     * @param boolean $messageread
     *
     * @return Messages
     */
    public function setMessageread($messageread)
    {
        $this->messageread = $messageread;

        return $this;
    }

    /**
     * Get messageread
     *
     * @return boolean
     */
    public function getMessageread()
    {
        return $this->messageread;
    }
}
