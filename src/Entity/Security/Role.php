<?php

namespace App\Entity\Security;

use App\Repository\Security\RoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"role_simple_list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"role_simple_list"})
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"role_simple_list"})
     */
    private $descripcion;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }
}
