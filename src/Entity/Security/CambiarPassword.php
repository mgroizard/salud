<?php

namespace App\Entity\Security;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class CambiarPassword extends EstadoUsuario
{
    public function __construct()
    {
        $this->setNombre('Cambiar Password');
        parent::__construct();
    }

    public function getRoles($userRoles = [])
    {        
        return ['ROLE_CHANGE_PASSWORD'];
    }

    public function mustChangePassword()
    {
        return true;
    }
}