<?php

namespace App\Controller;

use App\Entity\Agencia;
use App\Entity\Gerente;
use App\Repository\AgenciaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgenciaController extends AbstractController
{
    #[Route('/', name: 'app_listar_agencias')]
    public function index(AgenciaRepository $agencias): Response
    {
        return $this->render('agencia/listar_agencias.html.twig', [
            'agencias' => $agencias->findAll(),
        ]);
    }
    
    #[Route('/agencia/{id}', name: 'app_agencia')]
    public function agencia(AgenciaRepository $agencias, $id): Response
    {
        $agencias = $agencias->findAll();
        if (key_exists($id,$agencias)) {
            $agencia = $agencias[$id];
        }
        else {
            $agencia = null;
        }
        return $this->render('agencia/agencia.html.twig', [
            'agencia' => $agencia,
        ]);
    }

    // ========================================================================
    // AJUSTAR DEPOIS PARA CRIAÇÃO DE NOVAS AGÊNCIAS:
    // ========================================================================
    // #[Route('/agencia/criar', name: 'app_criar_agencia', priority:1)]
    // public function criar_agencia(AgenciaRepository $agencias, $id): Response
    // {
    //     $agencias = $agencias->findAll();
    //     return $this->render('agencia/criar_agencia.html.twig', [
    //         'agencia' => $agencias[$id],
    //     ]);
    // }
}
