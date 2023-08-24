<?php

namespace App\Controller;

use App\DTO\Security\UserRestorePassword;
use App\Entity\Security\CambiarPassword;
use App\Entity\Security\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/public")
 */
class PublicController extends BaseController
{
    /**
     * @Route("/email/activar/{user}/usuario/{slug}" , name="user_update", methods={"get"})
     */
    public function activateUser(Request $request, Usuario $user, string $slug ,EntityManagerInterface $em, MailerInterface $mailer): Response
    {   
        try {    
            
            $fecha = clone $user->getCreatedAt();
            $fecha->modify('+2 days');
  
            if(($user->getToken() == $slug) && ( $fecha >= new \DateTime()) && $user->needsToBeActivated())
            {
                $user->setEstado(new CambiarPassword());
                $em->flush();
                $email = (new TemplatedEmail())
                ->from('no-reply@msal.gba.gov.ar')
                ->to($user->getEmail())
                ->subject('Usuario Activado')
                ->htmlTemplate('emails/user/active.html.twig')
                ->context([
                    'user' => $user,
                ]);
                $mailer->send($email);
                
                return $this->render('user/user.advice.html.twig',['user' => $user, 'message' => 'Activación del usuario con éxito', 'title' => 'Activación de Usuario']);
            }
            return $this->render('user/user.error.html.twig',['user' => $user, 'error' => 'El usuario ya se encuentra activo', 'title' => 'Activación de Usuario']);
        } catch (\Exception $e) {
            return $this->render('user/user.error.html.twig',['user' => $user, 'error' => $e->getMessage(), 'title' => 'Activiación de Usuario']);
        }        
    }

    /**
     * @Route("/request/restore/password" , name="public_request_restore_password", methods={"post"})
     */
    public function requestRestorePassword(UserRestorePassword $restore, EntityManagerInterface $em, MailerInterface $mailer): Response
    {   
        $title = 'Solicitud de recupero de contraseña';
        try {    
            
            $user = $em->getRepository(User::class)->findOneByEmail($restore->email);
            
            if($user)
            {
                $user->generateToken();
                $em->flush();
                $email = (new TemplatedEmail())
                ->from('no-reply@msal.gba.gov.ar')
                ->to($user->getEmail())
                ->subject('Solicitud de Recupero de Contraseña')
                ->htmlTemplate('emails/user/restore.password.html.twig')
                ->context([
                    'user' => $user,
                    'url'  => $this->generateUrl('public_confirm_restore_password',['user' => $user->getId(), 'slug' => $user->getToken()],UrlGeneratorInterface::ABSOLUTE_URL)
                ]);
                $mailer->send($email);
                return $this->render('user/user.advice.html.twig',['user' => $user, 'message' => 'Solicitud enviada con éxito', 'title' => $title]);
            }

            return $this->render('user/user.error.html.twig',['user' => $user, 'error' => 'No existe el usuario', 'title' => $title]);
        } catch (\Exception $e) {
            return $this->render('user/user.error.html.twig',['user' => $user, 'error' => $e->getMessage(), 'title' => $title]);
        }        
    }

    /**
     * @Route("/{user}/confirmar/recupero/password/{slug}" , name="public_confirm_restore_password", methods={"get"})
     */
    public function confirmRestorePassword(Usuario $user, string $slug, EntityManagerInterface $em, MailerInterface $mailer,UserPasswordHasherInterface $passwordHasher): Response
    {   
        $title = 'Solicitud de recupero de contraseña';
        try {    
            
            if($user->getToken() == $slug)
            {
                $user->setEstado(new CambiarPassword());
                $password = strtoupper(substr(md5(uniqid('', true)), 0, 7));
                $user->setPassword($passwordHasher->hashPassword(
                    $user,
                    $password
                ));
                $em->flush();
                $email = (new TemplatedEmail())
                ->from('no-reply@msal.gba.gov.ar')
                ->to($user->getEmail())
                ->subject($title)
                ->htmlTemplate('emails/user/restore.password.confirmed.html.twig')
                ->context([
                    'user' => $user,
                    'password' => $password
                ]);
                $mailer->send($email);
                return $this->render('user/user.advice.html.twig',['user' => $user, 'message' => 'Confirmación de recupero de contraseña generada con éxito', 'title' => $title]);
            }

            return $this->render('user/user.error.html.twig',['user' => $user, 'error' => 'No existe el usuario', 'title' => $title]);
        } catch (\Exception $e) {
            return $this->render('user/user.error.html.twig',['user' => $user, 'error' => $e->getMessage(), 'title' => $title]);
        }        
    }
}
