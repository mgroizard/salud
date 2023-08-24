<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Activo extends EstadoUsuario
{
    public function __construct()
    {
        $this->setNombre('Activo');
        parent::__construct();
    }

    public function getRoles($userRoles = [])
    {
        $roles = [];
        
        foreach ($userRoles as $rol) {
            $roles[] = $rol->getNombre();
        }
        
        return array_unique($roles);
    }
}