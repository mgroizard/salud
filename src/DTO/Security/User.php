<?php 
namespace App\DTO\Security;

use App\DTO\AbstractRequest;
use App\Entity\Security\Role;
use App\Entity\Security\Usuario as SecurityUsuario;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;

class User extends AbstractRequest
{
    /**
     * @Assert\Email(
     *     message = "El email '{{ value }}' no es un email válido."
     * )
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
     * @Type("string")
     */
    public $password;

    /**
     * @Type("array")
     * @NotBlank()
     */
    public $roles;

    public function newUsuario(SecurityUsuario $creator, EntityManagerInterface $em)
    {
        $user = new SecurityUsuario();
        $user->setNombre($this->nombre)
             ->setApellido($this->apellido)
             ->setEmail($this->email)
             ->setCreatedBy($creator)
             ->generateToken()
             ;

        if($this->password){
            $user->setPassword($this->password);
        }

        foreach($this->roles as $roleId)
        {
            $role = $em->getRepository(Role::class)->find($roleId);
            if(!$role){
                throw new \Exception('Role not found id: ' . $roleId);
            }
            $user->addRole($role);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $mensaje = '';
            foreach($errors as $error){
                $mensaje = $error->getMessage();
            }
            throw new \Exception($mensaje);
        }
        return $user;
    }
}
