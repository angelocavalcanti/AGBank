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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remetente = null;

    #[ORM\ManyToOne(inversedBy: 'transacoes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conta $destinatario = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $data = null;

    #[ORM\Column]
    private ?float $valor = null;

    #[ORM\Column(length: 255)]
    private ?string $descricao = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemetente(): ?string
    {
        return $this->remetente;
    }

    public function setRemetente(?string $remetente): self
    {
        $this->remetente = $remetente;

        return $this;
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
}
