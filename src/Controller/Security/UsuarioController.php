<?php

namespace App\Controller\Security;

use App\Controller\BaseController;
use App\DTO\Security\User as UserDTO;
use App\DTO\Security\UserUpdate;
use App\DTO\Security\UserUpdatePassword;
use App\Entity\Security\Activo;
use App\Entity\Security\CambiarPassword;
use App\Entity\Security\Deshabilitado;
use App\Entity\Security\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/usuarios")
 */
class UsuarioController extends BaseController
{
    /**
     * 
     * Listado de usuarios del sistema.
     *
     * Listado de usuarios del sistema
     *
     * @OA\Response(
     *     response=200,
     *     description="Devuelve un listado de usuarios del sistema",
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("", methods={"GET"}, name="user_list")
     */ 
    public function list(Request $request,EntityManagerInterface $em)
    {
        return $this->successResponse($em->getRepository(Usuario::class)->findAll(),['user_simple_list']);
    }

    /**
     * @Route("" , name="user_create", methods={"post"})
     */
    public function create(UserDTO $userDTO,EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher,MailerInterface $mailer): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SALUD');

        try {    
            //creo el nuevo usuario con los datos del userDTO
            $user =  $userDTO->newUser($this->getUser(),$em);

            //Hasheo la Password     
            $password = $user->getPassword();          
            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            ));
            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
            ->from('no-respondery@salud.gba.gov.ar')
            ->to($user->getEmail())
            ->subject('Usuario Creado - Ministerio de Salud de la Provincia de Buenos Aires')
            ->htmlTemplate('emails/user/created.html.twig')
            ->context([
                'user' => $user,
                'url'  => $this->generateUrl('public_activate_user',['user'=>$user->getId(), 'slug' => $user->getToken()],UrlGeneratorInterface::ABSOLUTE_URL),
                'password' => $password,
            ]);

            $mailer->send($email);

            return $this->json(['message' => 'Usuario creado con éxito']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error al crear el usuario' , 'error' => $e->getMessage()]);
        }        
    }

    /**
     * @Route("/{id}" , name="user_update", methods={"put"})
     */
    public function update(Usuario $id = null,UserUpdate $userDTO,EntityManagerInterface $em): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SALUD');
        if(!$user = $id){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {    
            $userDTO->updateUser($user,$this->getUser(),$em);
            $em->flush();
            return $this->json(['message' => 'Usuario actualizado con éxito']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error al actualizar el usuario' , 'error' => $e->getMessage()]);
        }        
    }

 
    /**
     *  Cambia el estado de un usuario a Activo
     *
     *  Cambia el estado de un usuario a Activo
     *
     * @OA\Response(
     *     response=200,
     *     description="Cambia el estado de un usuario a Activo",
     * )
     * @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="El id de usuario",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("/{usuario}/activar" , name="user_activate", methods={"put"})
     */
    public function activate(Usuario $usuario = null,EntityManagerInterface $em): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SALUD');
        if(!$usuario){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {    

            $usuario->setEstado(new Activo());
            $em->flush();
            return $this->json(['message' => 'Usuario actualizado con éxito']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error al actualizar el usuario' , 'error' => $e->getMessage()]);
        }        
    }

    /**
     *  Cambia el estado de un usuario a Cambiar Password
     *
     *  Cambia el estado de un usuario a Cambiar Password, ésto obligará al usuario a cambiar la contraseña antes de poder relizar cualquier otra acción en el sistema
     *
     * @OA\Response(
     *     response=200,
     *     description="Cambia el estado de un usuario a Cambiar Password",
     * )
     * @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="El id de usuario",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("/{usuario}/cambiar/password" , name="user_change_password", methods={"put"})
     */
    public function changePassword(Usuario $usuario = null,EntityManagerInterface $em): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SALUD');
        if(!$usuario){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {   
            $usuario->setEstado(new CambiarPassword());
            $em->flush();
            return $this->json(['message' => 'Usuario actualizado con éxito']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error al actuailzar el usuario' , 'error' => $e->getMessage()]);
        }        
    }

    /**
     *  Cambia el estado de un usuario a Deshabilitado
     *
     *  Cambia el estado de un usuario a Deshabilitado, no podrá realizar ninguna acción en el sistema
     *
     * @OA\Response(
     *     response=200,
     *     description="Cambia el estado de un usuario a Deshabilitado",
     * )
     * @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="El id de usuario",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("/{usuario}/deshabilitar" , name="user_dissable", methods={"put"})
     */
    public function dissable(Usuario $usuario = null,EntityManagerInterface $em): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SALUD');
        if(!$usuario){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {    
            $usuario->setEstado(new Deshabilitado());
            $em->flush();
            return $this->json(['message' => 'User updated successfuly']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Failed to update the user' , 'error' => $e->getMessage()]);
        }        
    }

    /**
     * @Route("/{id}/actualizar/password" , name="user_update_password", methods={"put"})
     */
    public function udpatePassword(UserUpdatePassword $updateData, Usuario $id = null,EntityManagerInterface $em,UserPasswordHasherInterface $passwordHasher): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_CHANGE_PASSWORD');
        if(!$user = $id){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {    
            $updateData->updatePassword($user,$this->getUser(),$em,$passwordHasher);
            $user->setEstado(new Activo());
            $em->flush();
            return $this->json(['message' => 'Contraseña actualizada con éxito']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error al actualizar la contraseña' , 'error' => $e->getMessage()]);
        }        
    }

    /**
     * 
     *  Devuelve la información del usuario autenticado en el sistema
     *
     *  Devuelve la información del usuario autenticado en el sistema
     *
     * @OA\Response(
     *     response=200,
     *     description=" Devuelve la información del usuario autenticado en el sistema",
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("/info/logged" , name="user_info_logged", methods={"get"})
     */
    public function userLoggedInfo(Request $request): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_CHANGE_PASSWORD');
        
        try {
            return $this->successResponse($this->getUser(),['user_simple_list','user_status_list','role_simple_list']);
        } catch (\Exception $e) {
            return $this->returnErrorResponse('Error al obtener la información','user', $e->getMessage(),500);
        }        
    }

    /**
     *  Devuelve la información del usuario enviado por parámetro
     *
     *  Devuelve la información del usuario enviado por parámetro
     *
     * @OA\Response(
     *     response=200,
     *     description=" Devuelve la información del usuario enviado por parámetro",
     * )
     * @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="El id de usuario",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("/{usuario}" , name="user_info", methods={"get"})
     */
    public function userInfo(Request $request, Usuario $usuario = null): JsonResponse
    {   
        
        $this->denyAccessUnlessGranted('ROLE_CHANGE_PASSWORD');
        if(!$usuario){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {
            return $this->successResponse($usuario,['user_simple_list','user_status_list','role_simple_list']);
        } catch (\Exception $e) {
            return $this->returnErrorResponse('Error al obtener la información','user', $e->getMessage(),500);
        }        
    }
}
