<?php

namespace App\Controller;

use App\Entity\Conta;
use App\Entity\Transacao;
use App\Form\ContaType;
use App\Form\DepositoType;
use App\Repository\AgenciaRepository;
use App\Repository\ContaRepository;
use App\Repository\GerenteRepository;
use App\Repository\TransacaoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContaController extends AbstractController
{
    // CRIAR NOVA CONTA EM ALGUMA AGÊNCIA DO BANCO
    #[Route('/conta/criar', name: 'app_criar_conta')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function criar_conta(ContaRepository $contas, Request $request): Response
    {
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Usuário ADMIN não pode abrir conta.');
                return $this->redirectToRoute('app_login');
            }elseif(in_array('ROLE_GERENTE', $roles)){
                $this->addFlash('error', 'Usuário GERENTE não pode abrir conta.');
                return $this->redirectToRoute('app_login');
            }
        }
        $formConta = $this->createForm(ContaType::class, new Conta());
        $formConta->handleRequest($request);
        if(!$this->getUser()){
            $this->addFlash('success', 'Faça login ou registre-se para solicitar abertura de Conta.');
            return $this->redirectToRoute('app_login');
        }
        else if ($formConta->isSubmitted() && $formConta->isValid()) {
            $conta = new Conta();
            $conta = $formConta->getData();
            $conta->setNumero(rand(1000, 10000));
            while ($contas->findOneBy(['numero'=> $conta->getNumero()])){
                $conta->setNumero(rand(1000, 10000));
            }
            $conta->setSaldo(0);
            $conta->setUser($this->getUser());
            // $conta->setAprovada(false); // Já inicializada no construtor da classe como False
            $contas->save($conta, true);
            $agencia = $conta->getAgencia();
            $tipo = $conta->getTipo();
            $this->addFlash('success', 'Sucesso! Conta solicitada. Aguarde aprovação.');
            $this->addFlash('success', 'Conta '.$tipo->getTipo().': '.$conta->getNumero().'. Agência: '.$agencia->getCodigo().' ('.$agencia->getNome().')');
            return $this->redirectToRoute('app_listar_contas');
        }
        return $this->renderForm('conta/criar_conta.html.twig', [
            'formConta' => $formConta, 
        ]);
    }

    // EFETUAR DEPÓSITO PÚBLICO (SEM ESTAR LOGADO NO SISTEMA)
    #[Route('/conta/depositar', name: 'app_depositar_conta')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function depositoPublico(ContaRepository $contas, Request $request, TransacaoRepository $transacoes, AgenciaRepository $agencias): Response
    {
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Usuário ADMIN não pode efetuar transações.');
                return $this->redirectToRoute('app_login');
            }
        }        
        $formDeposito = $this->createForm(DepositoType::class, new Transacao());
        $formDeposito->handleRequest($request);
        if ($formDeposito->isSubmitted() && $formDeposito->isValid()) {
            $valor = $formDeposito->get('valor')->getData();
            $conta = $_REQUEST['conta'];
            $agencia = $_REQUEST['agencia'];
            $agenciaDestinatario = $agencias->findOneBy(array('codigo' => $agencia));
            if($agenciaDestinatario){
                $contaDestinatario = $contas->findOneBy(array('numero' => $conta));
                if($contaDestinatario){
                    if($contaDestinatario->getAgencia() == $agenciaDestinatario){
                        if($contaDestinatario->isAprovada()){
                            if($valor > 0 && is_numeric($valor)){  
                                $transacao = new Transacao();
                                $transacao->setDescricao('Depósito'); 
                                $transacao->setRemetente(null);//Não há objeto Conta para público 
                                $transacao->setDestinatario($contaDestinatario);
                                $transacao->setValor($valor);
                                $transacao->setResponsavel('Público Geral');
                                $contaDestinatario->creditar($valor);
                                $contas->save($contaDestinatario, true);
                                $transacoes->save($transacao, true);

                                $this->addFlash('success', 'Sucesso! Valor depositado.');
                                $this->addFlash('success', 'Depositado R$'.number_format($valor, 2, ',', '.').' na Conta '.$contaDestinatario->getTipo().' '.$contaDestinatario. ' da Agência '.$agenciaDestinatario. ' ('.$agenciaDestinatario->getCodigo().')');
                                return $this->redirectToRoute('app_listar_agencias');
                            }
                            else {
                                $this->addFlash('error', 'Erro! Valor não pode ser negativo.');
                            }
                        }
                        else{
                            $this->addFlash('error', 'Erro! Conta ainda não está aprovada.');
                        }
                    }
                    else {
                        $this->addFlash('error', 'Erro! Conta não pertence à Agência escolhida.');
                    }
                }
                else {
                    $this->addFlash('error', 'Erro! Conta não encontrada.');
                }
            }
            else{
                $this->addFlash('error', 'Erro! Agência não encontrada.');
            }
        } 
        return $this->renderForm('conta/depositar_conta.html.twig', [
            'formDeposito' => $formDeposito
        ]);
    }

    // LISTAR TODAS AS CONTAS DO USUÁRIO LOGADO (SE GERENTE -> TODAS AS CONTAS DA AGÊNCIA)
    #[Route('/conta/listar', name: 'app_listar_contas')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function listar(ContaRepository $contas, GerenteRepository $gerentes): Response
    {
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Usuário ADMIN não possui contas.');
                return $this->redirectToRoute('app_login');
            }
            else if(in_array('ROLE_GERENTE', $roles)){
                $gerente = $gerentes->findOneBy(['user' => $this->getUser()]);
                return $this->render('conta/listar_contas.html.twig', [
                    'contas' => $contas->findBy(['agencia' => $gerente->getAgencia()], ['aprovada' => 'ASC']),
                    'ehGerente' => true,
                ]);        
            }
        }
        return $this->render('conta/listar_contas.html.twig', [
            'contas' => $contas->findBy(['user' => $this->getUser()], ['aprovada' => 'DESC', 'dataAbertura' => 'DESC']),
            'ehGerente' => false,
        ]);
    }
    
    // EFETUAR TRANSFERÊNCIA DA CONTA DO USUÁRIO PARA OUTRA CONTA
    #[Route('/conta{id}/transferir', name: 'app_transferir_conta')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function transferencia($id, ContaRepository $contas, AgenciaRepository $agencias, TransacaoRepository $transacoes, Request $request): Response
    {
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Usuário ADMIN não pode efetuar transações.');
                return $this->redirectToRoute('app_login');
            }
        }
        $contaRemetente = $contas->findOneBy(['id' => $id]);
        $formTransferir = $this->createForm(DepositoType::class, new Transacao());
        $formTransferir->handleRequest($request);
        if ($formTransferir->isSubmitted() && $formTransferir->isValid()) {
            $valor = $formTransferir->get('valor')->getData();
            $agencia = $_REQUEST['agencia'];
            $conta = $_REQUEST['conta'];
            $agenciaDestinatario = $agencias->findOneBy(array('codigo' => $agencia));
            if($agenciaDestinatario){
                $contaDestinatario = $contas->findOneBy(array('numero' => $conta));
                if($contaDestinatario){
                    if($contaDestinatario->getAgencia() == $agenciaDestinatario){
                        if($contaRemetente != $contaDestinatario){
                            if($contaDestinatario->isAprovada()){
                                if ($valor > 0 && is_numeric($valor)){ 
                                    if($contaRemetente->getSaldo() >= $valor){  
                                        $transacao = new Transacao();
                                        $transacao->setDescricao('Transferência'); 
                                        $transacao->setRemetente($contaRemetente);
                                        $transacao->setDestinatario($contaDestinatario);
                                        $transacao->setValor($valor);
                                        in_array('ROLE_GERENTE', $roles) ? $transacao->setResponsavel('Gerente') : $transacao->setResponsavel('Cliente');
                                        $contaRemetente->transferir($valor, $contaDestinatario);
                                        $contas->save($contaDestinatario, true);
                                        $contas->save($contaRemetente, true);
                                        $transacoes->save($transacao, true);
                                        $this->addFlash('success', 'Sucesso! Valor transferido.');
                                        $this->addFlash('success', 'Transferido R$'.number_format($valor, 2, ',', '.').' da conta '.$contaRemetente.' para a conta '.$contaDestinatario. ' da Agência '.$agenciaDestinatario. ' ('.$agenciaDestinatario->getCodigo().')');
                                        return $this->redirectToRoute('app_listar_contas');
                                    }
                                    else {
                                        $this->addFlash('error', 'Erro! Saldo insuficiente.');
                                    }
                                }
                                else {
                                    $this->addFlash('error', 'Erro! Valor precisa ser maior que zero.');
                                }
                            }
                            else{
                                $this->addFlash('error', 'Erro! Conta destinatária ainda não está aprovada.');
                            }
                        }
                        else{
                            $this->addFlash('error', 'Erro! Conta remetente precisa ser diferente da conta de destino.');
                        }
                    }
                    else {
                        $this->addFlash('error', 'Erro! Conta não pertence à Agência escolhida.');
                    }
                }
                else {
                    $this->addFlash('error', 'Erro! Conta não encontrada.');
                }
            }
            else {
                $this->addFlash('error', 'Erro! Agência não encontrada.');
            }
        } 
        return $this->renderForm('conta/transferir_conta.html.twig', [
            'formTransferir' => $formTransferir,
        ]);
    }    

    // EFETUAR CRÉDITO NA CONTA DO USUÁRIO
    #[Route('/conta{id}/creditar', name: 'app_creditar_conta')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function credito($id, ContaRepository $contas, TransacaoRepository $transacoes, Request $request): Response
    {
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Usuário ADMIN não pode efetuar transações.');
                return $this->redirectToRoute('app_login');
            }
        }
        $contaRemetente = $contas->findOneBy(['id' => $id]);
        if($contaRemetente->isAprovada()){
            $formCreditar = $this->createForm(DepositoType::class, new Transacao());
            $formCreditar->handleRequest($request);
            if ($formCreditar->isSubmitted() && $formCreditar->isValid()) {
                $valor = $formCreditar->get('valor')->getData();
                if ($valor > 0 && is_numeric($valor)){  
                    $transacao = new Transacao();
                    $transacao->setDescricao('Crédito'); 
                    $transacao->setRemetente($contaRemetente);
                    $transacao->setDestinatario($contaRemetente);
                    $transacao->setValor($valor);
                    in_array('ROLE_GERENTE', $roles) ? $transacao->setResponsavel('Gerente') : $transacao->setResponsavel('Cliente');
                    $contaRemetente->creditar($valor);
                    $contas->save($contaRemetente, true);
                    $transacoes->save($transacao, true);
                    $this->addFlash('success', 'Sucesso! Valor creditado.');
                    $this->addFlash('success', 'Creditado R$'.number_format($valor, 2, ',', '.').' na conta '.$contaRemetente.' da Agência '.$contaRemetente->getAgencia(). ' ('.$contaRemetente->getAgencia()->getCodigo().')');
                    return $this->redirectToRoute('app_listar_contas');
                }
                else {
                    $this->addFlash('error', 'Erro! Valor precisa ser maior que zero.');
                }
            }
        }
        else {
            $this->addFlash('error', 'Erro! Conta ainda não está aprovada.');
        } 
        return $this->renderForm('conta/creditar_conta.html.twig', [
            'formCreditar' => $formCreditar,
        ]);
    }    

    // EFETUAR DÉBITO NA CONTA DO USUÁRIO
    #[Route('/conta{id}/debitar', name: 'app_debitar_conta')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function debito($id, ContaRepository $contas, TransacaoRepository $transacoes, Request $request): Response
    {
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Usuário ADMIN não pode efetuar transações.');
                return $this->redirectToRoute('app_login');
            }
        }
        $contaRemetente = $contas->findOneBy(['id' => $id]);
        if($contaRemetente->isAprovada()){
            $formDebitar = $this->createForm(DepositoType::class, new Transacao());
            $formDebitar->handleRequest($request);
            if ($formDebitar->isSubmitted() && $formDebitar->isValid()) {
                $valor = $formDebitar->get('valor')->getData();
                if ($valor > 0 && is_numeric($valor)){
                    if($contaRemetente->getSaldo() >= $valor){
                        $transacao = new Transacao();
                        $transacao->setDescricao('Débito'); 
                        $transacao->setRemetente($contaRemetente);
                        $transacao->setDestinatario($contaRemetente);
                        $transacao->setValor($valor);
                        in_array('ROLE_GERENTE', $roles) ? $transacao->setResponsavel('Gerente') : $transacao->setResponsavel('Cliente');
                        $contaRemetente->debitar($valor);
                        $contas->save($contaRemetente, true);
                        $transacoes->save($transacao, true);
                        $this->addFlash('success', 'Sucesso! Valor debitado.');
                        $this->addFlash('success', 'Debitado R$'.number_format($valor, 2, ',', '.').' na conta '.$contaRemetente.' da Agência '.$contaRemetente->getAgencia(). ' ('.$contaRemetente->getAgencia()->getCodigo().')');
                        return $this->redirectToRoute('app_listar_contas');
                    }else{
                        $this->addFlash('error', 'Erro! Saldo insuficiente.');
                    }
                }
                else {
                    $this->addFlash('error', 'Erro! Valor precisa ser maior que zero.');
                }
            }
        } else{
            $this->addFlash('error', 'Erro! Conta ainda não está aprovada.');
        }
        return $this->renderForm('conta/debitar_conta.html.twig', [
            'formDebitar' => $formDebitar,
        ]);
    }    

    // EXCLUIR CONTA
    #[Route('/conta{id}/excluir', name: 'app_excluir_conta')]
    #[IsGranted('ROLE_USER')]
    public function excluir($id, Conta $conta, ContaRepository $contas): Response
    {
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_ADMIN', $roles)){
                $this->addFlash('error', 'Usuário ADMIN não pode efetuar transações.');
                return $this->redirectToRoute('app_login');
            }
        }
        if($contas->findOneBy(['id' => $id])){
            $contas->remove($conta, true);
            $this->addFlash('success', 'Sucesso! Conta removida.');    
        }     
        else{
            $this->addFlash('error', 'Erro! Conta não removida, tente novamente.');
        }
        return $this->redirectToRoute('app_listar_contas');
    }

    #[Route('conta/transacoes{id}', name: 'app_transacoes')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function transacoes($id, TransacaoRepository $transacoes, GerenteRepository $gerentes): Response
    {
        $historico = $transacoes->findBy(['remetente' => $id]);
        $historico = array_merge($historico, $transacoes->findBy(['destinatario' => $id, 'descricao' => 'Transferência']));
        $historico = array_merge($historico, $transacoes->findBy(['destinatario' => $id, 'descricao' => 'Depósito']));
        if($this->getUser() ? true : false){
            $roles = $this->getUser()->getRoles();
            if(in_array('ROLE_GERENTE', $roles)){
                $gerente = $gerentes->findOneBy(['user' => $this->getUser()]);
                return $this->render('conta/transacoes.html.twig', [
                    'transacoes' => $historico,
                    'ehGerente' => true,
                    'id' => $id
                ]);        
            }
        }
        return $this->render('conta/transacoes.html.twig', [
            'transacoes' => $historico,
            'ehGerente' => false,
            'id' => $id
        ]);
    }
}
