<?php

namespace App\Controller;

use App\Entity\Conta;
use App\Entity\Transacao;
use App\Form\ContaType;
use App\Form\DepositoType;
use App\Repository\AgenciaRepository;
use App\Repository\ContaRepository;
use App\Repository\TransacaoRepository;
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
            $conta->setDataAbertura(new \DateTime()); // Associa a data de criação do objeto à de abertura da conta
            $contas->save($conta, true);
            $agencia = $conta->getAgencia();
            $tipo = $conta->getTipo();
            $this->addFlash('success', 'Sucesso! Conta criada.');
            $this->addFlash('success', 'Conta '.$tipo->getTipo().': '.$conta->getNumero().'. Agência: '.$agencia->getCodigo().' ('.$agencia->getNome().')');
            return $this->redirectToRoute('app_listar_contas');
        }
        // RETORNAR PARA PÁGINA DE LOGIN CASO NÃO ESTEJA LOGADO PARA ABRIR CONTA
        return $this->renderForm('conta/criar_conta.html.twig', [
            'formConta' => $formConta, 
        ]);
    }

    #[Route('/conta/depositar', name: 'app_depositar_conta')]
    public function depositar(ContaRepository $contas, Request $request, TransacaoRepository $transacoes, AgenciaRepository $agencias): Response
    {
        $formDeposito = $this->createForm(DepositoType::class, new Transacao());
        $formDeposito->handleRequest($request);
        if ($formDeposito->isSubmitted() && $formDeposito->isValid()) {
            $valor = $formDeposito->get('valor')->getData();
            $conta = $_REQUEST['conta'];
            $agencia = $_REQUEST['agencia'];
            $agenciaEncontrada = $agencias->findOneBy(array('codigo' => $agencia));
            if($agenciaEncontrada){
                $contaEncontrada = $contas->findOneBy(array('numero' => $conta));
                if($contaEncontrada){
                    if($contaEncontrada->getAgencia() == $agenciaEncontrada){
                        if ($valor > 0 && is_numeric($valor)){  
                            $transacao = new Transacao();
                            $transacao->setDescricao('Depósito público'); 
                            $transacao->setRemetente('Público'); 
                            $transacao->setData(new \DateTime());
                            $transacao->setDestinatario($contaEncontrada);
                            $transacao->setValor($valor);
                            $contaEncontrada->setSaldo($contaEncontrada->getSaldo() + $valor);
                            $contas->save($contaEncontrada, true);
                            $transacoes->save($transacao, true);

                            $this->addFlash('success', 'Sucesso! Valor depositado.');
                            $this->addFlash('success', 'Depositado R$'.number_format($valor, 2, ',', '.').' na conta '.$contaEncontrada. ' da Agência '.$agenciaEncontrada. ' ('.$agenciaEncontrada->getCodigo().')');
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

    // LISTAR TODAS AS CONTAS EXISTENTES DE TODAS AS AGÊNCIA:
    #[Route('/conta/listar', name: 'app_listar_contas')]
    public function listar(ContaRepository $contas): Response
    {
        return $this->render('conta/listar_contas.html.twig', [
            'contas' => $contas->findAll(),
        ]);
    }
    
    #[Route('/conta/transferir', name: 'app_transferir_conta')]
    public function transferir(ContaRepository $contas, AgenciaRepository $agencias, TransacaoRepository $transacoes, Request $request): Response
    {
        $formTransferir = $this->createForm(DepositoType::class, new Transacao());
        $formTransferir->handleRequest($request);
        if ($formTransferir->isSubmitted() && $formTransferir->isValid()) {
            $valor = $formTransferir->get('valor')->getData();
            $agencia = $_REQUEST['agencia'];
            $conta = $_REQUEST['conta'];
            $agenciaEncontrada = $agencias->findOneBy(array('codigo' => $agencia));
            if($agenciaEncontrada){
                $contaEncontrada = $contas->findOneBy(array('numero' => $conta));
                if($contaEncontrada){
                    if($contaEncontrada->getAgencia() == $agenciaEncontrada){
                        if ($valor > 0 && is_numeric($valor)){ 
                            // if( user.conta->getSaldo() >= valor){  
                                $transacao = new Transacao();
                                $transacao->setDescricao('Transferência'); 
                                $transacao->setRemetente('Usuário'); //user.conta
                                $transacao->setData(new \DateTime());
                                $transacao->setDestinatario($contaEncontrada);
                                $transacao->setValor($valor);
                                //$user.conta->setSaldo($contaEncontrada->getSaldo() - $valor);
                                $contaEncontrada->setSaldo($contaEncontrada->getSaldo() + $valor);
                                $contas->save($contaEncontrada, true);
                                //$contas->save($user.conta, true);
                                $transacoes->save($transacao, true);

                                $this->addFlash('success', 'Sucesso! Valor transferido.');
                                // $this->addFlash('success', 'Transferido R$'.number_format($valor, 2, ',', '.').' da conta '.$user.conta.' para a conta '.$contaEncontrada. ' da Agência '.$agenciaEncontrada. ' ('.$agenciaEncontrada->getCodigo().')');
                                return $this->redirectToRoute('app_listar_contas');
                            // }
                            // else {
                            //     this->addFlash('error', 'Erro! Saldo insuficiente.');
                            // }
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
        return $this->renderForm('conta/transferir_conta.html.twig', [
            'formTransferir' => $formTransferir,
            'contas' => $contas
        ]);
        
        //  FALTA ALTERAR E AJUSTAR AINDA ACIMA ***
        // =========================================================
    }    

    // EXCLUIR CONTA: 
    #[Route('/conta{id}/excluir', name: 'app_excluir_conta')]
    public function excluir($id, Conta $conta, ContaRepository $contas): Response
    {
    $contas->remove($conta, true);
    if(!$contas->findOneBy(['id' => $id])){
        $this->addFlash('success', 'Sucesso! Conta removida.');    
    }     
    else{
        $this->addFlash('error', 'Erro! Conta não removida, tente novamente.');
    }
    return $this->redirectToRoute('app_listar_contas');
    }
}
