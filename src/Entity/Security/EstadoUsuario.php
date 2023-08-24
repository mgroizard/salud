<?php

namespace App\Entity\Security;

use App\Repository\Security\EstadoUsuarioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=EstadoUsuarioRepository::class)
 * @ORM\Table(indexes={ @ORM\Index(name="estado_nombre", columns={"nombre"}),
 *                      @ORM\Index(name="search_discr", columns={"discr"})
 *            }
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *                          "creado"           = "Creado",
 *                          "cambiar_password" = "CambiarPassword", 
 *                          "activo"           = "Activo", 
 *                          "deshabilitado"    = "Deshabilitado"
 *                      })
 */
abstract class EstadoUsuario
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user_simple_list"})
     */
    
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"user_simple_list"})
     */
    private $nombre;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"user_simple_list"})
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="estados")
     */
    private $usuario;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getRoles($userRoles = [])
    {
        return [];
    }

    public function mustChangePassword()
    {
        return false;
    }

    public function needsToBeActivated()
    {
        return false;
    }
}
