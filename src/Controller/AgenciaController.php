<?php

namespace App\Controller;

use App\Entity\Agencia;
use App\Entity\Gerente;
use App\Entity\User;
use App\Form\AgenciaType;
use App\Form\GerenteType;
use App\Form\RegistrationFormType;
use App\Repository\AgenciaRepository;
use App\Repository\GerenteRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
   
    // EDITAR DADOS DE AGÊNCIA: 
    #[Route('/agencia{id}/editar', name: 'app_editar_agencia')]
    #[IsGranted('ROLE_GERENTE')]
    #[IsGranted('ROLE_ADMIN')]
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
     #[IsGranted('ROLE_ADMIN')]
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
    
    // CRIAR AGÊNCIA
    #[Route('/agencia/criar', name: 'app_criar_agencia', priority:1)]
    #[IsGranted('ROLE_ADMIN')]
    public function criar(AgenciaRepository $agencias, GerenteRepository $gerentes, UserRepository $users, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {        
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(!in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Erro! Sem permissão de acesso a esta página.');
                return $this->redirectToRoute('app_login');
            }
        }      
        $formAgencia = $this->createForm(AgenciaType::class, new Agencia());
        $formGerente = $this->createForm(GerenteType::class, new Gerente());
        $formUser = $this->createForm(RegistrationFormType::class, new User());

        //o formulário foi submetido? 
        $formAgencia->handleRequest($request);
        $formGerente->handleRequest($request);
        $formUser->handleRequest($request);
        
        //se sim, tratar a submissão
        if ($formAgencia->isSubmitted() && $formAgencia->isValid() && $formGerente->isSubmitted() && $formGerente->isValid() && $formUser->isSubmitted() && $formUser->isValid()) {
            $user = $formUser->getData();
            $gerente = $formGerente->getData();
            $agencia = $formAgencia->getData();
            $agencia->setGerente($gerente); // Associa o gerente criado à Agência criada
            $user->setNome($gerente->getNome());
            $user->setCpf($gerente->getCpf());
            $user->setRoles(['ROLE_GERENTE']);
            $user->setPassword($userPasswordHasher->hashPassword($user,$formUser->get('plainPassword')->getData()));
            $user->setIsVerified(true); // opcional
            $users->save($user, true);
            $gerente->setUser($user);
            $gerentes->save($gerente, true);
            $agencias->save($agencia, true);
            $this->addFlash('success', 'Sucesso! Agência e Usuário Gerente criados.');
            return $this->redirectToRoute('app_listar_agencias');
        }
        //caso contrário, renderizar o formulário para adicionar Agências
        return $this->renderForm('agencia/criar_agencia.html.twig', [
            'formAgencia' => $formAgencia,
            'formGerente' => $formGerente,
            'formUser' => $formUser
        ]);
    }
}