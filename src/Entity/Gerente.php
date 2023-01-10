<?php

namespace App\Entity;

use App\Repository\GerenteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GerenteRepository::class)]
class Gerente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nome = null;

    #[ORM\Column(length: 15)]
    private ?string $cpf = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $matricula = null;

    #[ORM\OneToOne(mappedBy: 'gerente', cascade: ['persist', 'remove'])]
    private ?Agencia $agencia = null;

    public function __toString()
    {
        return $this->nome;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): self
    {
        $this->cpf = $cpf;

        return $this;
    }

    public function getMatricula(): ?string
    {
        return $this->matricula;
    }

    public function setMatricula(?string $matricula): self
    {
        $this->matricula = $matricula;

        return $this;
    }

    public function getAgencia(): ?Agencia
    {
        return $this->agencia;
    }

    public function setAgencia(Agencia $agencia): self
    {
        // set the owning side of the relation if necessary
        if ($agencia->getGerente() !== $this) {
            $agencia->setGerente($this);
        }

        $this->agencia = $agencia;

        return $this;
    }
}
