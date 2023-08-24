<?php 
namespace App\DTO\Security;

use App\DTO\AbstractRequest;
use App\Entity\Security\Role;
use App\Entity\Security\Usuario as SecurityUsuario;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;

class UserUpdate extends AbstractRequest
{
    /**
     * @Assert\Email(message: 'The email {{ value }} is not a valid email.')
     * @NotBlank()
     */
    public $email;

    /**
     * @Type("string")
     * @NotBlank()
     */
    public $nombre;
    
    /**
     * @Type("string")
     * @NotBlank()
     */
    public $apellido;

    /**
     * @Type("array")
     * @NotBlank()
     */
    public $roles;

    public function updateUser(SecurityUsuario $userToUpdate, SecurityUsuario $user, EntityManagerInterface $em)
    {
        $userToUpdate->setNombre($this->nombre)
             ->setApellido($this->apellido)
             ->setEmail($this->email)
             ->setUpdatedBy($user)
             ;
        
        //quito todos los reoles
        foreach($userToUpdate->getRolesToUpdate() as $role){
            $userToUpdate->removeRole($role);
        }     

        //agrego los roles enviados
        foreach($this->roles as $roleId)
        {
            $role = $em->getRepository(Role::class)->find($roleId);
            if(!$role){
                throw new \Exception('Role not found id: ' . $roleId);
            }
            $userToUpdate->addRole($role);
        }

        $errors = $this->validator->validate($userToUpdate);
        if (count($errors) > 0) {
            $mensaje = '';
            foreach($errors as $error){
                $mensaje = $error->getMessage();
            }
            throw new \Exception($mensaje);
        }
        return $userToUpdate;
    }
}
