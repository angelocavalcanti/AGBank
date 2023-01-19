<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function login(AuthenticationUtils $utils): Response
    {
        $ultimoUsuario = $utils->getLastUsername();
        $erro = $utils->getLastAuthenticationError();

        return $this->render('login/index.html.twig', [
            'ultimoUsuario' => $ultimoUsuario,
            'erro' => $erro
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {

    }
}
