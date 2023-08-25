<?php

namespace App\Entity\Security;

use App\Repository\Security\TipoDocumentoRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=TipoDocumentoRepository::class)
 * @UniqueEntity(
 *     fields={"tipo"},
 *     errorPath="tipo",
 *     message="El tipo de documento '{{ value }}' ya se encuentra en uso."
 * )
 */
class TipoDocumento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"tipodocumento_simple_list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15, unique=true)
     * @Groups({"tipodocumento_simple_list"})
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"tipodocumento_simple_list"})
     */
    private $nombre;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }
}
