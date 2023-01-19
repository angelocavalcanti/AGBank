<?php

namespace App\DataFixtures;

use App\Entity\Agencia;
use App\Entity\Conta;
use App\Entity\Gerente;
use App\Entity\TipoConta;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher){

    }

    public function load(ObjectManager $manager): void
    {
        // $agencia1 = new Agencia();
        // $agencia1->setNome('Centro');
        // $agencia1->setTelefone('(87)99988-7766');
        // $agencia1->setEndereco('Av. principal, 100, Centro, Petrolina/PE');
        // $agencia1->setCodigo('001');
        // $manager->persist($agencia1);

        // $agencia2 = new Agencia();
        // $agencia2->setNome('Integração');
        // $agencia2->setTelefone('(87)99957-2134');
        // $agencia2->setEndereco('Rua Cavalcanti, 70, Maria Auxiliadora, Petrolina/PE');
        // $agencia2->setCodigo('123');
        // $manager->persist($agencia2);

        // $agencia3 = new Agencia();
        // $agencia3->setNome('Vila');
        // // $agencia3->setTelefone('(87)99988-7766'); // Comentado propositalmente
        // $agencia3->setEndereco('Rua Gomes, 11, Vila, Juazeiro/BA');
        // $agencia3->setCodigo('321');
        // $manager->persist($agencia3);

        $tipoConta = new TipoConta();
        $tipoConta->setTipo('Corrente');
        $manager->persist($tipoConta);
        
        $tipoConta = new TipoConta();
        $tipoConta->setTipo('Poupança');
        $manager->persist($tipoConta);

        $tipoConta = new TipoConta();
        $tipoConta->setTipo('Salário');
        $manager->persist($tipoConta);

        $user = new User();
        $user->setNome('Angelo Gustavo');
        $user->setCpf('123.456.789-10');
        $user->setEmail('admin@admin.com');
        $user->setPassword($this->hasher->hashPassword($user, '123'));
        $user->setTelefone('87988117733');
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);
        
        $manager->flush();

        for ($i = 0; $i < 10; $i++) {        
            $gerente = new Gerente();
            $gerente->setNome('Angelo '.$i);
            $gerente->setCpf('123.456.789.1'.$i);
            $gerente->setMatricula('107'.$i);
            $manager->persist($gerente);

            $agencia = new Agencia();
            $agencia->setCodigo('200'.$i);
            $agencia->setEndereco('Rua Onze, '.$i.'1, Centro, Petrolina/PE');
            $agencia->setNome('Ag '. $i);
            $agencia->setTelefone('8798734-1963');
            $agencia->setGerente($gerente);
            $manager->persist($agencia);

            $userGerente = new User();
            $userGerente->setNome('Nome Gerente '.$i);
            $userGerente->setCpf($gerente->getCpf());
            $userGerente->setEmail('gerente'.$i.'@agbank.com');
            $userGerente->setPassword($this->hasher->hashPassword($userGerente, '123'));
            $userGerente->setTelefone('87988117'.$i);
            $userGerente->setGerente($gerente);
            $userGerente->setRoles(['ROLE_GERENTE']);
            $manager->persist($userGerente);

            $user = new User();
            $user->setNome('Nome Usuário '.$i);
            $user->setCpf('1112223334'.$i);
            $user->setEmail('usuario'.$i.'@agbank.com');
            $user->setPassword($this->hasher->hashPassword($user, '123'));
            $user->setTelefone('8799911070'.$i);
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);

            $conta = new Conta();
            $conta->setNumero('100'.$i);
            $conta->setSaldo(0);
            $conta->setAgencia($agencia);
            $conta->setTipo($tipoConta);
            $conta->setUser($user);
            $manager->persist($conta);

            $manager->flush();
        }
    }
}
