<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Deshabilitado extends EstadoUsuario
{
    public function __construct()
    {
        $this->setNombre('Deshabilitado');
        parent::__construct();
    }
}