<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Creado extends EstadoUsuario
{
    public function __construct()
    {
        $this->setNombre('Creado');
        parent::__construct();
    }

    public function needsToBeActivated()
    {
        return true;
    }
}