<?php

namespace App\Controller;

use App\Entity\Conta;
use App\Form\ContaType;
use App\Repository\ContaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContaController extends AbstractController
{
    // #[Route('/conta', name: 'app_conta')]
    // public function index(): Response
    // {
    //     return $this->render('conta/index.html.twig', [
    //         'controller_name' => 'ContaController',
    //     ]);
    // }

    #[Route('/conta/criar', name: 'app_criar_conta')]
    public function criar_conta(ContaRepository $contas, Request $request): Response
    {
        $formConta = $this->createForm(ContaType::class, new Conta());
        $formConta->handleRequest($request);
        if ($formConta->isSubmitted() && $formConta->isValid()) {
            $conta = $formConta->getData();
            $conta->setNumero(5);
            $conta->setSaldo(0);
            $conta->setDataAbertura(new \DateTime()); // Associa a data de abertura da conta à de criação do objeto
            $contas->save($conta, true);
            $this->addFlash('success', 'Sucesso! Conta criada.');
            return $this->redirectToRoute('app_listar_agencias');
        }
        // RETORNAR PARA PÁGINA DE LOGIN CASO NÃO ESTEJA LOGADO PARA ABRIR CONTA
        return $this->renderForm('conta/criar_conta.html.twig', [
            'formConta' => $formConta, 
        ]);
    }
}
