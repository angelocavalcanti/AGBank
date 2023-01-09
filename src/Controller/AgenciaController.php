<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgenciaController extends AbstractController
{
    #[Route('/', name: 'app_lista_agencias')]
    public function index(): Response
    {
        return $this->render('agencia/index.html.twig', [
            'agencias' => $this->agencias,
        ]);
    }

    private array $agencias = [
        ['nome'=>'Centro', 'telefone'=>'879988-7766', 'endereco'=>'Av. principal, 100, Centro, Brasília', 'codigo'=>'001'],
        ['nome'=>'Rio', 'telefone'=>'879957-2134', 'endereco'=>'Rua Cavalcanti, 70, Rio, Brasília', 'codigo'=>'123'],
        ['nome'=>'Vila', 'telefone'=>'877734-4751', 'endereco'=>'Rua Gomes, 11, Vila, Brasília', 'codigo'=>'321']
    ];
    
    #[Route('/agencia/{id}', name: 'app_agencia')]
    public function agencia($id): Response
    {
        if (key_exists($id,$this->agencias)) {
            $agencia = $this->agencias[$id];
        }
        else {
            $agencia = [ 
                'nome' => "Esta Agência não existe na base de dados.", 'telefone' => '--', 'endereco' => '--', 'codigo'=>'--'
            ];
        }
        return $this->render('agencia/agencia.html.twig', [
            'agencia' => $agencia,
        ]);
    }
}
