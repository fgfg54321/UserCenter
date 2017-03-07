<?php

namespace UserCenterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('UserCenterBundle:Default:index.html.twig');
    }
}
