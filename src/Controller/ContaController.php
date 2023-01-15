<?php

namespace App\Controller;

use App\Entity\Agencia;
use App\Entity\Conta;
use App\Form\ContaType;
use App\Repository\AgenciaRepository;
use App\Repository\ContaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContaController extends AbstractController
{
    #[Route('/conta/criar', name: 'app_criar_conta')]
    public function criar_conta(ContaRepository $contas, Request $request): Response
    {
        $formConta = $this->createForm(ContaType::class, new Conta());
        $formConta->handleRequest($request);
        if ($formConta->isSubmitted() && $formConta->isValid()) {
            $conta = $formConta->getData();
            $conta->setNumero(rand(1000, 10000));
            while ($contas->findOneBy(['numero'=> $conta->getNumero()])){
                $conta->setNumero(rand(1000, 10000));
            }
            $conta->setSaldo(0);
            $conta->setDataAbertura(new \DateTime()); // Associa a data de abertura da conta à de criação do objeto
            $contas->save($conta, true);
            $agencia = $conta->getAgencia();
            $tipo = $conta->getTipo();
            $this->addFlash('success', 'Sucesso! Conta criada.');
            $this->addFlash('success', 'Conta '.$tipo->getTipo().': '.$conta->getNumero().'. Agência: '.$agencia->getCodigo().' ('.$agencia->getNome().')');
            return $this->redirectToRoute('app_listar_agencias');
        }
        // RETORNAR PARA PÁGINA DE LOGIN CASO NÃO ESTEJA LOGADO PARA ABRIR CONTA
        return $this->renderForm('conta/criar_conta.html.twig', [
            'formConta' => $formConta, 
        ]);
    }

    #[Route('/conta/depositar', name: 'app_conta_deposito')]
    public function depositar($numeroConta, ContaRepository $contas): Response
    {
        // encontrar conta e fazer o depósito aqui:
        // falta implementar
        $contas->findOneBy($numeroConta);

        return $this->render('conta/listar_agencias.html.twig', [
            'controller_name' => 'ContaController',
        ]);
    }
}
