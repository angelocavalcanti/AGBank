<?php

namespace App\Controller;

use App\Entity\Conta;
use App\Entity\Transacao;
use App\Form\ContaType;
use App\Form\DepositoType;
use App\Repository\AgenciaRepository;
use App\Repository\ContaRepository;
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
                        if($valor > 0 && is_numeric($valor)){  
                            $transacao = new Transacao();
                            $transacao->setDescricao('Depósito público'); 
                            $transacao->setRemetente(null);//Não há objeto Conta para público 
                            $transacao->setDestinatario($contaDestinatario);
                            $transacao->setValor($valor);
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
                    else {
                        $this->addFlash('error', 'Erro! Conta não pertence à Agência escolhida.');
                    }
                }
                else {
                    $this->addFlash('error', 'Erro! Conta não encontrada.');
                }
            }else{
                $this->addFlash('error', 'Erro! Agência não encontrada.');
            }
        } 
        return $this->renderForm('conta/depositar_conta.html.twig', [
            'formDeposito' => $formDeposito
        ]);
    }

    // LISTAR TODAS AS CONTAS DO USUÁRIO LOGADO
    #[Route('/conta/listar', name: 'app_listar_contas')]
    public function listar(ContaRepository $contas): Response
    {
        return $this->render('conta/listar_contas.html.twig', [
            'contas' => $contas->findBy(['user' => $this->getUser()]),
        ]);
    }
    
    // EFETUAR TRANSFERÊNCIA DA CONTA DO USUÁRIO PARA OUTRA CONTA
    #[Route('/conta{id}/transferir', name: 'app_transferir_conta')]
    public function transferencia($id, ContaRepository $contas, AgenciaRepository $agencias, TransacaoRepository $transacoes, Request $request): Response
    {
        $user = $this->getUser();
        $contaRemetente = $contas->findOneBy(['user' => $user, 'id' => $id]);
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
                            if ($valor > 0 && is_numeric($valor)){ 
                                if($contaRemetente->getSaldo() >= $valor){  
                                    $transacao = new Transacao();
                                    $transacao->setDescricao('Transferência'); 
                                    $transacao->setRemetente($contaRemetente); // VERIFICAR *** SE FOI GERENTE
                                    $transacao->setDestinatario($contaDestinatario);
                                    $transacao->setValor($valor);
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
            else{
                $this->addFlash('error', 'Erro! Agência não encontrada.');
            }
        } 
        return $this->renderForm('conta/transferir_conta.html.twig', [
            'formTransferir' => $formTransferir,
        ]);
    }    

    // EFETUAR CRÉDITO NA CONTA DO USUÁRIO
    #[Route('/conta{id}/creditar', name: 'app_creditar_conta')]
    public function credito($id, ContaRepository $contas, TransacaoRepository $transacoes, Request $request): Response
    {
        $user = $this->getUser();
        $contaRemetente = $contas->findOneBy(['user' => $user, 'id' => $id]);
        $formCreditar = $this->createForm(DepositoType::class, new Transacao());
        $formCreditar->handleRequest($request);
        if ($formCreditar->isSubmitted() && $formCreditar->isValid()) {
            $valor = $formCreditar->get('valor')->getData();
            if ($valor > 0 && is_numeric($valor)){  
                $transacao = new Transacao();
                $transacao->setDescricao('Crédito'); 
                $transacao->setRemetente($contaRemetente);// VERIFICAR *** SE FOI GERENTE
                $transacao->setDestinatario($contaRemetente);
                $transacao->setValor($valor);
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
        return $this->renderForm('conta/creditar_conta.html.twig', [
            'formCreditar' => $formCreditar,
        ]);
    }    

    // EFETUAR DÉBITO NA CONTA DO USUÁRIO
    #[Route('/conta{id}/debitar', name: 'app_debitar_conta')]
    public function debito($id, ContaRepository $contas, TransacaoRepository $transacoes, Request $request): Response
    {
        $user = $this->getUser();
        $contaRemetente = $contas->findOneBy(['user' => $user, 'id' => $id]);
        $formDebitar = $this->createForm(DepositoType::class, new Transacao());
        $formDebitar->handleRequest($request);
        if ($formDebitar->isSubmitted() && $formDebitar->isValid()) {
            $valor = $formDebitar->get('valor')->getData();
            if ($valor > 0 && is_numeric($valor)){
                if($contaRemetente->getSaldo() >= $valor){
                    $transacao = new Transacao();
                    $transacao->setDescricao('Debitado R$'.number_format($valor, 2, ',', '.').' na conta '.$contaRemetente.' da Agência '.$contaRemetente->getAgencia(). ' ('.$contaRemetente->getAgencia()->getCodigo().')'); 
                    $transacao->setRemetente($contaRemetente);// VERIFICAR *** SE FOI GERENTE
                    $transacao->setDestinatario($contaRemetente);
                    $transacao->setValor($valor);
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
        return $this->renderForm('conta/debitar_conta.html.twig', [
            'formDebitar' => $formDebitar,
        ]);
    }    

    // EXCLUIR CONTA 
    #[Route('/conta{id}/excluir', name: 'app_excluir_conta')]
    #[IsGranted('ROLE_GERENTE')]
    public function excluir($id, Conta $conta, ContaRepository $contas): Response
    {
        // $roles = $this->getUser().roles;
        // if($roles[] == 'ROLE_GERENTE' ){ // VERIFICAR SE É O GERENTE DA AGÊNCIA DA CONTA ***

        // } 
        if($contas->findOneBy(['id' => $id])){
            $contas->remove($conta, true);
            $this->addFlash('success', 'Sucesso! Conta removida.');    
        }     
        else{
            $this->addFlash('error', 'Erro! Conta não removida, tente novamente.');
        }
        return $this->redirectToRoute('app_listar_contas');
    }
}
