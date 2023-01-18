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
    // LISTAR TODAS AS AGÊNCIAS:
    #[Route('/', name: 'app_listar_agencias')]
    public function index(AgenciaRepository $agencias): Response
    {
        return $this->render('agencia/listar_agencias.html.twig', [
            'agencias' => $agencias->findAll(),
        ]);
    }
   
    // VER INFORMAÇÕES DE AGÊNCIA:
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
   
    // CRIAR AGÊNCIA:
    #[Route('/agencia/criar', name: 'app_criar_agencia', priority:1)]
    public function criar(AgenciaRepository $agencias, GerenteRepository $gerentes, Request $request): Response
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
   
    // EDITAR DADOS DE AGÊNCIA: 
    #[Route('/agencia{id}/editar', name: 'app_editar_agencia')]
    public function editar($id, Agencia $agencia, Request $request, AgenciaRepository $agencias): Response
    {
        $formAgencia = $this->createForm(AgenciaType::class, $agencia);
        $formAgencia->handleRequest($request);
        if ($formAgencia->isSubmitted() && $formAgencia->isValid()) {
            $agencia = $formAgencia->getData();
            $agencias->save($agencia, true);
            $this->addFlash('success', 'Sucesso! Os dados da Agência foram atualizados.');
            return $this->redirectToRoute('app_listar_agencias');
        }
        return $this->renderForm('agencia/editar_agencia.html.twig', [
            'formAgencia' => $formAgencia,
            'agencia' => $agencia,
            'id' => $id
        ]);
    }

     // EXCLUIR AGÊNCIA: 
     #[Route('/agencia{id}/excluir', name: 'app_excluir_agencia')]
     public function excluir($id, Agencia $agencia,  AgenciaRepository $agencias): Response
     {
        $agencias->remove($agencia, true);
        if(!$agencias->findOneBy(['id' => $id])){
            $this->addFlash('success', 'Sucesso! Agência removida.');    
        }     
        else{
            $this->addFlash('error', 'Erro! Agência não removida, tente novamente.');
        }
        return $this->redirectToRoute('app_listar_agencias');
     }
}