<?php

namespace App\Controller;

use App\Entity\Gerente;
use App\Form\GerenteType;
use App\Repository\GerenteRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GerenteController extends AbstractController
{
    #[Route('/gerente', name: 'app_listar_gerentes')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function listar_gerentes(GerenteRepository $gerentes): Response
    {
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); // Nega acesso se user logado não estiver o papel [IS_AUTHENTICATED_FULLY]
        return $this->render('gerente/listar_gerentes.html.twig', [
            'gerentes' => $gerentes->findAll(),
        ]);
    }

    #[Route('/gerente{id}/editar', name: 'app_editar_gerente')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editar($id, Gerente $gerente, Request $request, GerenteRepository $gerentes): Response
    {
        $formGerente = $this->createForm(GerenteType::class, $gerente);
        $formGerente->handleRequest($request);
        if ($formGerente->isSubmitted() && $formGerente->isValid()) {
            $gerente = $formGerente->getData();
            $gerentes->save($gerente, true);
            $this->addFlash('success', 'Os dados do Gerente foram atualizados!');
            return $this->redirectToRoute('app_listar_gerentes');
        }
        return $this->renderForm('gerente/editar_gerente.html.twig',[
            'formGerente' => $formGerente, 
            'gerente' => $gerente, 
            'id' => $id 
        ]);
    }
}
