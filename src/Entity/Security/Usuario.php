<?php

namespace App\Entity\Security;

use App\Repository\Security\UsuarioRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Security\Log;
use App\Entity\Security\EstadoUsuario;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * 
 * @ORM\Entity(repositoryClass=UsuarioRepository::class)
 * @ORM\Table(indexes={ @ORM\Index(name="email_idx", columns={"email"}),
 *                      @ORM\Index(name="created_at_index", columns={"created_at"})
 *            })
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="El email '{{ value }}' ya se encuentra en uso."
 * )
 */ 
class Usuario implements JWTUserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * 
     * @Groups({"user_simple_list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"user_simple_list"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user_simple_list"})
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user_simple_list"})
     */
    private $apellido;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"user_simple_list"})
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @Groups({"user_simple_list"})
     */
    private $created_by;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"user_simple_list"})
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @Groups({"user_simple_list"})
     */
    private $updated_by;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity=EstadoUsuario::class, mappedBy="usuario")
     */
    private $estados;

    /**
     * @ORM\OneToMany(targetEntity=Log::class, mappedBy="usuario")
     */
    private $logs;

    /**
     * @ORM\ManyToOne(targetEntity=EstadoUsuario::class, cascade={"all"})
     * @Groups({"user_simple_list"})
     */
    private $estado;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class)
     */
    private $roles;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"user_simple_list"})
     */
    private $ultimoLogin;

    public function __construct()
    {
        $this->estados = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->setEstado(new Creado());
        $this->password = strtoupper(substr(md5(uniqid('', true)), 0, 7));
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): self
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function getCreatedBy(): ?self
    {
        return $this->created_by;
    }

    public function setCreatedBy(?self $created_by): self
    {
        $this->created_by = $created_by;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUpdatedBy(): ?self
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?self $updated_by): self
    {
        $this->updated_by = $updated_by;
        $this->updated_at = new \DateTimeImmutable();

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection<int, EstadoUsuario>
     */
    public function getEstadoUsuarios(): Collection
    {
        return $this->estados;
    }

    public function addEstadoUsuario(EstadoUsuario $estadoUsuario): self
    {
        if (!$this->estados->contains($estadoUsuario)) {
            $this->estados[] = $estadoUsuario;
            $estadoUsuario->setUsuario($this);
        }

        return $this;
    }

    public function removeEstadoUsuario(EstadoUsuario $estadoUsuario): self
    {
        if ($this->estados->removeElement($estadoUsuario)) {
            // set the owning side to null (unless already changed)
            if ($estadoUsuario->getUsuario() === $this) {
                $estadoUsuario->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setUsuario($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getUsuario() === $this) {
                $log->setUsuario(null);
            }
        }

        return $this;
    }

    public function getEstado(): ?EstadoUsuario
    {
        return $this->estado;
    }

    public function setEstado(?EstadoUsuario $estado): self
    {
        //solamente cambia el estado si es diferente al anterior
        if(!$this->estado || ($this->estado->getNombre() != $estado->getNombre())) 
        {
            $this->estado = $estado;
            $estado->setUsuario($this);
        }

        return $this;
    }

    /**
     * Get Roles implement Interface
     *
     */
    public function getRoles(): array
    {   
        return $this->estado->getRoles($this->roles);
    }

    public function eraseCredentials()
    {
    }

    public static function createFromPayload($username, array $payload)
    {
        return new self(
            $username,
        );
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function isActive()
    {
        return (sizeof($this->getRoles()) > 0);
    }
    
    public function getRolesToUpdate()
    {
        return $this->roles;
    }
    
    /**
     * @Groups({"user_simple_list"})
     */
    public function getUserRoles()
    {
        return $this->roles;
    }

    public function generateToken()
    {
        $this->token = base64_encode(json_encode([
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt()->format('d/m/Y H:i:s'),
            'now' => new \DateTime()
        ]));
    }

    /**
     * @Groups({"user_simple_list"})
     */
    public function getMustChangePassword()
    {
        return $this->estado->mustChangePassword();
    }

    /**
     * @Groups({"user_simple_list"})
     */
    public function getNeedsToBeActivated()
    {
        return $this->estado->needsToBeActivated();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getEmail();
    }

    public function getUsername(): string
    {
        return (string) $this->getEmail();
    }

    /**
     * Returning a salt is only needed if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function equals($usuario)
    {
        if ($this->nombre !== $usuario->getUsername()) {
            return false;
        }

       return true;
    }

    /**
     * @return Collection<int, EstadoUsuario>
     */
    public function getEstados(): Collection
    {
        return $this->estados;
    }

    public function addEstado(EstadoUsuario $estado): self
    {
        if (!$this->estados->contains($estado)) {
            $this->estados[] = $estado;
            $estado->setUsuario($this);
        }

        return $this;
    }

    public function removeEstado(EstadoUsuario $estado): self
    {
        if ($this->estados->removeElement($estado)) {
            // set the owning side to null (unless already changed)
            if ($estado->getUsuario() === $this) {
                $estado->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"user_simple_list"})
     */
    public function getHistorialEstados()
    {
        return $this->estados;
    }

    public function getUltimoLogin(): ?\DateTimeInterface
    {
        return $this->ultimoLogin;
    }

    public function setUltimoLogin(?\DateTimeInterface $ultimoLogin): self
    {
        $this->ultimoLogin = $ultimoLogin;

        return $this;
    }

    public function setLastLogin()
    {
        $this->ultimoLogin = new \DateTimeImmutable();
    }
}
