<?php 
namespace App\DTO\Security;

use App\DTO\AbstractRequest;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class UserRestorePassword extends AbstractRequest
{
    /**
     * @Assert\Email(message: 'The email {{ value }} is not a valid email.')
     * @NotBlank()
     */
    public $email;
}
