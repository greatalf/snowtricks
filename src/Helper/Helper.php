<?php

namespace App\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Helper extends AbstractController
{
    /**
     * @param $entity
     * @return bool
     */
    public function entityExists($entity)
    {
        if(!is_object($entity))
            return false;

        return $entity->getId() !== null;
    }

    public function redirectWithFlash($route, $messageType, $message)
    {
        $this->addFlash($messageType, $message);
        return $this->redirect($this->generateUrl($route) . '#msg_flash');
    }
}