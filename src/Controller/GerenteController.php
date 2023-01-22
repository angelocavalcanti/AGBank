<?php

namespace App\Controller;

use App\Entity\Gerente;
use App\Entity\User;
use App\Form\GerenteType;
use App\Repository\ContaRepository;
use App\Repository\GerenteRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GerenteController extends AbstractController
{
    #[Route('/gerentes', name: 'app_listar_gerentes')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function listar_gerentes(GerenteRepository $gerentes): Response
    {
        return $this->render('gerente/listar_gerentes.html.twig', [
            'gerentes' => $gerentes->findAll(),
        ]);
    }

    #[Route('/gerente', name: 'app_gerente')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function gerente(GerenteRepository $gerentes): Response
    {
        $user = $this->getUser();
        $gerente = $gerentes->findOneBy(['user' => $user]);
        return $this->render('gerente/gerente.html.twig', [
            'gerente' => $gerente,
            'usuario' => $user
        ]);
    }

    #[Route('/gerente{id}/editar', name: 'app_editar_gerente')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editar($id, Gerente $gerente, Request $request, GerenteRepository $gerentes, UserRepository $users): Response
    {
        $formGerente = $this->createForm(GerenteType::class, $gerente);
        $formGerente->handleRequest($request);
        if ($formGerente->isSubmitted() && $formGerente->isValid()) {
            $gerente = $formGerente->getData();
            $userGerente = $gerente->getUser();
            $userGerente->setCpf($gerente->getCpf());
            $userGerente->setNome($gerente->getNome());
            $users->save($userGerente, true);
            $gerentes->save($gerente, true);
            $this->addFlash('success', 'Os dados do Gerente foram atualizados!');
            return $this->redirectToRoute('app_listar_agencias');
        }
        return $this->renderForm('gerente/editar_gerente.html.twig',[
            'formGerente' => $formGerente, 
            'gerente' => $gerente, 
            'id' => $id 
        ]);
    }

    #[Route('/liberar{id}', name: 'app_liberar_conta')]
    #[IsGranted('ROLE_GERENTE')]
    public function liberar_conta($id, ContaRepository $contas, GerenteRepository $gerentes): Response
    {
        $gerente = $gerentes->findOneBy(['user' => $this->getUser()]);
        $conta = $contas->findOneBy(['id' => $id]);
        if($conta){
            if($conta->getAgencia() == $gerente->getAgencia()){
                $conta->setAprovada(true);
                $contas->save($conta, true);
                $this->addFlash('success', 'Conta ' . $conta . ' aprovada.');
                return $this->render('conta/listar_contas.html.twig', [
                    'contas' => $contas->findBy(['agencia' => $gerente->getAgencia()], ['aprovada' => 'ASC']),
                    'ehGerente' => true,
                ]);
            }
            else{
                $this->addFlash('error', 'Sem permissão. Conta pertence à outra agência.');    
            }
        }
        else{
            $this->addFlash('error', 'Conta não encontrada.');
        }
        return $this->redirectToRoute('app_listar_agencias');
    }

}
