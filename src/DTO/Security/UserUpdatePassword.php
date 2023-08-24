<?php 
namespace App\DTO\Security;

use App\DTO\AbstractRequest;
use App\Entity\Security\Usuario as SecurityUser;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserUpdatePassword extends AbstractRequest
{
    /**
     * @Type("string")
     * @NotBlank()
     */
    public $old_password;

    /**
     * @Type("string")
     * @NotBlank()
     * @Assert\Length(null,6,30)
     */
    public $new_password;

    public function updatePassword(SecurityUser $userToUpdate, SecurityUser $updaterUser, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $passwordValid = $passwordHasher->isPasswordValid($userToUpdate, $this->old_password);

        if (!$passwordValid) {
            throw new \Exception('Password mismatch');
        }

        $re = '/
            # Match password with 6-15 chars with letters and digits
            ^                 # Anchor to start of string.
            (?=.*?[A-Z])      # Assert there is at least one mayus letter, AND
            (?=.*?[a-z])      # Assert there is at least one minus letter, AND
            (?=.*?[0-9])      # Assert there is at least one digit, AND
            (?=.{6,30}\z)     # Assert the length is from 6 to 30 chars.
            /x';

        if(!preg_match($re, $this->new_password)) {
            throw new \Exception('The password must have at least 6 characters, at least one capital letter, at least one minucule, at least one number, it only admits alphanumeric charset, and last but not least, it hast to be at least 6 chars length and no bigger than 30 chars length');
        }
        
        $userToUpdate->setPassword($passwordHasher->hashPassword(
                                    $userToUpdate,
                                    $this->new_password
                                ))
                       ->setUpdatedBy($updaterUser)
                       ;
        
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
