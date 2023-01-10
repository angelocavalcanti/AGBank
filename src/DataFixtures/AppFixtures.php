<?php

namespace App\DataFixtures;

use App\Entity\Agencia;
use App\Entity\Gerente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
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

        for ($i = 0; $i < 10; $i++) {        
            $gerente = new Gerente();
            $gerente->setNome('Gustavo '.$i);
            $gerente->setCpf('123.456.789.1'.$i);
            $gerente->setMatricula('007'.$i);
            $manager->persist($gerente);

            $agencia = new Agencia();
            $agencia->setCodigo('00'.$i);
            $agencia->setEndereco('Rua Onze, '.$i.', Maravilha, Petrolina/PE');
            $agencia->setNome('Maravilha '. $i);
            $agencia->setTelefone('8798734-1963');
            $agencia->setGerente($gerente);
            $manager->persist($agencia);
            
            $manager->flush();
        }
    }
}
