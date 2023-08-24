<?php

namespace App\Entity\Security;

use App\Repository\Security\LogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LogRepository::class)
 */
class Log
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $ip;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $activityGroup;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="logs")
     */
    private $usuario;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

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

    public function getActivity(): ?string
    {
        return $this->activity;
    }

    public function setActivity(?string $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function getActivityGroup(): ?string
    {
        return $this->activityGroup;
    }

    public function setActivityGroup(string $activityGroup): self
    {
        $this->activityGroup = $activityGroup;

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

         /***
     *  @return array
     *  [
     *    'route_name_1',
     *    'route_name_2',
     *    .....
     *    'route_name_n',
     *  ];
     */
    public static function blackListRoutes(): array
    {
        return [
            '',
            'login',
        ];
    }

    public static function isRouteLoggable($route = ''): bool
    {
        return !array_key_exists($route, self::blackListRoutes());
    }

    public function __construct($user, $ip, $activityGroup, $activity)
    {
        $this->usuario = $user;
        $this->ip = $ip;
        $this->activityGroup = $activityGroup;
        $this->activity = $activity;
        $this->created_at = new \DateTimeImmutable();
    }
}
