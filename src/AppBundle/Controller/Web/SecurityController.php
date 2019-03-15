<?php

namespace AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends Controller
{

    /**
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
        return $this->render('users/login.html.twig');
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {

    }
}
