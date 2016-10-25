<?php
/**
 * Created by PhpStorm.
 * User: mateuszgra
 * Date: 2016-06-09
 * Time: 00:01
 */

namespace ChatBundle\Controller;


use ChatBundle\Entity\Messages;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\BrowserKit\Request;

class MessagesController extends Controller
{
    /**
     * @Route("/messages", name="message_request")
     */
    public function messagesAction(Request $request){
        /*$message = new Messages();*/

        $data = $request->getParameters();

        return( array(
            'data'=>$data
        ));

        /*$em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();*/
    }
}