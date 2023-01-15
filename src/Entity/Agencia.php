<?php

namespace App\Entity;

use App\Repository\AgenciaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AgenciaRepository::class)]
class Agencia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Este campo não pode estar em branco.')]
    #[Assert\Length(min:3, max:25, minMessage: 'Nome curto, precisa digitar no mínimo 3 caracteres.')]
    private ?string $nome = null;

    #[ORM\Column(length: 15)]
    #[Assert\Length(min:3, max:255, minMessage: 'Código curto, precisa digitar no mínimo 3 caracteres.')]
    #[Assert\NotBlank(message: 'Este campo não pode estar em branco.')]
    private ?string $codigo = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min:10, max:255, minMessage: 'Insira o endereço completo, com no mínimo 10 caracteres.')]
    private ?string $endereco = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telefone = null;

    #[ORM\OneToOne(inversedBy: 'agencia', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gerente $gerente = null;

    #[ORM\OneToMany(mappedBy: 'agencia', targetEntity: Conta::class, orphanRemoval: true)]
    private Collection $contas;

    public function __construct()
    {
        $this->contas = new ArrayCollection();
    }

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

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function setEndereco(string $endereco): self
    {
        $this->endereco = $endereco;

        return $this;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function setTelefone(?string $telefone): self
    {
        $this->telefone = $telefone;

        return $this;
    }

    public function getGerente(): ?Gerente
    {
        return $this->gerente;
    }

    public function setGerente(Gerente $gerente): self
    {
        $this->gerente = $gerente;

        return $this;
    }

    /**
     * @return Collection<int, Conta>
     */
    public function getContas(): Collection
    {
        return $this->contas;
    }

    public function addConta(Conta $conta): self
    {
        if (!$this->contas->contains($conta)) {
            $this->contas->add($conta);
            $conta->setAgencia($this);
        }

        return $this;
    }

    public function removeConta(Conta $conta): self
    {
        if ($this->contas->removeElement($conta)) {
            // set the owning side to null (unless already changed)
            if ($conta->getAgencia() === $this) {
                $conta->setAgencia(null);
            }
        }

        return $this;
    }
}
