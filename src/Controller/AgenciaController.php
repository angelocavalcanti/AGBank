<?php

namespace App\Controller;

use App\Repository\AgenciaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgenciaController extends AbstractController
{
    #[Route('/', name: 'app_lista_agencias')]
    public function index(AgenciaRepository $agencias): Response
    {
        return $this->render('agencia/index.html.twig', [
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
            $agencia = [ 
                'nome' => "Esta Agência não existe na base de dados.", 'telefone' => '--', 'endereco' => '--', 'codigo'=>'--'
            ];
        }
        return $this->render('agencia/agencia.html.twig', [
            'agencia' => $agencia,
        ]);
    }
}
