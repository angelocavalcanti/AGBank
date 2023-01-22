<?php

namespace App\Entity;

use App\Repository\TransacaoRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TransacaoRepository::class)]
class Transacao
{
    public function __construct()
    {
        $this->data = new DateTime();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transacoes')]
    private ?Conta $destinatario = null;

    #[ORM\ManyToOne(inversedBy: 'transacoesr')]
    private ?Conta $remetente = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $data = null;

    #[ORM\Column]
    private ?float $valor = null;

    #[ORM\Column(length: 255)]
    private ?string $descricao = null;

    #[ORM\Column(length: 255)]
    private ?string $responsavel = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getDestinatario(): ?Conta
    {
        return $this->destinatario;
    }

    public function setDestinatario(?Conta $destinatario): self
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    public function getRemetente(): ?Conta
    {
        return $this->remetente;
    }

    public function setRemetente(?Conta $remetente): self
    {
        $this->remetente = $remetente;

        return $this;
    }
    
    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(\DateTimeInterface $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getValor(): ?float
    {
        return $this->valor;
    }

    public function setValor(float $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getResponsavel(): ?string
    {
        return $this->responsavel;
    }

    public function setResponsavel(string $responsavel): self
    {
        $this->responsavel = $responsavel;

        return $this;
    }
}
