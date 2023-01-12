<?php

namespace App\Controller;

use App\Entity\Agencia;
use App\Entity\Gerente;
use App\Form\AgenciaType;
use App\Form\GerenteType;
use App\Repository\AgenciaRepository;
use App\Repository\GerenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/agencia/criar', name: 'app_criar_agencia', priority:1)]
    public function criar_agencia(AgenciaRepository $agencias, GerenteRepository $gerentes, Request $request): Response
    {
        $formAgencia = $this->createForm(AgenciaType::class, new Agencia());
        $formGerente = $this->createForm(GerenteType::class, new Gerente());

        
        //o formulário foi submetido? 
        $formAgencia->handleRequest($request);
        $formGerente->handleRequest($request);
        //se sim, tratar a submissão
        if ($formAgencia->isSubmitted() && $formAgencia->isValid() && $formGerente->isSubmitted() && $formGerente->isValid()) {
            $gerente = $formGerente->getData();
            $gerentes->save($gerente, true);
            $agencia = $formAgencia->getData();
            $agencia->setGerente($gerente); // Associa o gerente criado à Agência a ser criada na linha abaixo
            $agencias->save($agencia, true);
            $this->addFlash('success', 'Sucesso! Agência criada com Gerente associado.');
            return $this->redirectToRoute('app_listar_agencias');
        }
        //caso contrário, renderizar o formulário para adicionar Agências
        return $this->renderForm('agencia/criar_agencia.html.twig', [
            'formAgencia' => $formAgencia,
            'formGerente' => $formGerente 
        ]);
    }
}
