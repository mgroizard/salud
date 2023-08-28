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
     *  @OA\Response(
     *     response=200,
     *     description="Devuelve la información del usuario enviado por parámetro",
     *     @OA\JsonContent(
     *        type="array",
     *         @OA\Items(ref=@Model(type=Usuario::class, groups={"user_simple_list","user_status_list","role_simple_list","tipodocumento_simple_list"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="length",
     *     in="query",
     *     description="Longitud máxima de resultados a devolver, valor por defecto si no es enviado 100",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="start",
     *     in="query",
     *     description="Desde qué página quiere el resultado de la consulta, valor por defecto si no es enviado 0",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="order",
     *     in="query",
     *     description="Parámetro para ordenar el resultado, pueden ser, id, nombre, apellido, email, ultimoLogin, created_at, el valor por defecto si no es enviado o es incorrecto es id",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="dir",
     *     in="query",
     *     description="Parámetro para el sentido en el que se ordena el resultado, pueden ser ASC y DESC, valor por defecto si no es enviado, DESC",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("", methods={"GET"}, name="user_list")
     */ 
    public function list(Request $request,EntityManagerInterface $em)
    {
        return $this->successResponse($em->getRepository(Usuario::class)->getQueryCollection($request),['user_simple_list','role_simple_list','tipodocumento_simple_list']);
    }

    /**
     *  Crea un nuevo Usuario
     *
     *  Crea un nuevo Usuario
     *
     * @OA\Response(
     *     response=200,
     *     description="Usuario creado con éxito",
     * )
     * @OA\RequestBody(
     *     description="Datos para crear un usuario",
     *     @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          type="object",
     *          required={"email","nombre","apellido","roles","nro_documento","tipo_documento"},
     *          @OA\Property(
     *             property="email",
     *             description="Email único entre los demas usuarios del sistema",
     *             type="number",
     *             example="johndoe@mail.com"
     *          ),
     *          @OA\Property(
     *             property="nombre",
     *             description="Nombre del usuario",
     *             type="string",
     *             example="Juan"
     *          ),
     *          @OA\Property(
     *             property="apellido",
     *             description="Apellido del usuario",
     *             type="string",
     *             example="Pérez"
     *          ),
     *          @OA\Property(
     *             property="nro_documento",
     *             description="Número de Documento",
     *             type="string",
     *             example="25417452"
     *          ),
     *          @OA\Property(
     *             property="tipo_documento",
     *             description="Identificador del Tipo de Documento",
     *             type="integer",
     *             example=1
     *          ),
     *          @OA\Property(
     *             property="password",
     *             description="Contraseña del usuario, puede dejarse en blanco y se genera una automáticamente",
     *             type="string",
     *             example="p@ssw0rDtoCh4n63"
     *          ),
     *          @OA\Property(
     *             property="roles",
     *             description="Rol o roles del usuario dentro del sistema",
     *             type="array",
     *             @OA\Items(
     *                
     *                    type="integer",
     *                    description="Identificador del rol en el sistema",
     *                    example=1
     *                 
     *             ),
     *             example="[1]"
     *          ),
     *       )
     *     )
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("" , name="user_create", methods={"post"})
     */
    public function create(UserDTO $userDTO,EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher,MailerInterface $mailer): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SALUD');

        try {    
            //creo el nuevo usuario con los datos del userDTO
            $user =  $userDTO->newUsuario($this->getUser(),$em);

            //Hasheo la Password     
            $password = $user->getPassword();          
            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            ));
            $em->persist($user);
            $em->flush();

            $email = (new TemplatedEmail())
            ->from($this->getParameter('no_reply_address'))
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
     *  Actualiza un Usuario
     *
     *  Actualiza un Usuario
     *
     * @OA\Response(
     *     response=200,
     *     description="Usuario actualizado con éxito",
     * )
     * @OA\Parameter(
     *     name="usuario",
     *     in="path",
     *     description="El id de usuario",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="Datos para actualizar un usuario",
     *     @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(
     *          type="object",
     *          required={"email","nombre","apellido","roles","nro_documento","tipo_documento"},
     *          @OA\Property(
     *             property="email",
     *             description="Email único entre los demas usuarios del sistema",
     *             type="number",
     *             example="johndoe@mail.com"
     *          ),
     *          @OA\Property(
     *             property="nombre",
     *             description="Nombre del usuario",
     *             type="string",
     *             example="Juan"
     *          ),
     *          @OA\Property(
     *             property="apellido",
     *             description="Apellido del usuario",
     *             type="string",
     *             example="Pérez"
     *          ),
     *          @OA\Property(
     *             property="nro_documento",
     *             description="Número de Documento",
     *             type="string",
     *             example="25417452"
     *          ),
     *          @OA\Property(
     *             property="tipo_documento",
     *             description="Identificador del Tipo de Documento",
     *             type="integer",
     *             example=1
     *          ),
     *          @OA\Property(
     *             property="roles",
     *             description="Rol o roles del usuario dentro del sistema",
     *             type="array",
     *             @OA\Items(
     *                
     *                    type="integer",
     *                    description="Identificador del rol en el sistema",
     *                    example=1
     *                 
     *             ),
     *             example="[1]"
     *          ),
     *       )
     *     )
     * )
     * @OA\Tag(name="Usuarios")
     * @Security(name="Bearer")
     * 
     * @Route("/{usuario}" , name="user_update", methods={"put"})
     */
    public function update(Usuario $usuario = null,UserUpdate $userDTO,EntityManagerInterface $em): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_ADMIN_SALUD');
        if(!$usuario){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {    
            $userDTO->updateUser($usuario,$this->getUser(),$em);
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
     *  Actualiza la contraseña el usuario
     *
     *  Actualiza la contraseña el usuario
     *
     * @OA\Response(
     *     response=200,
     *     description="Contraseña actualizada con éxito",
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
     * @Route("/{usuario}/actualizar/password" , name="user_update_password", methods={"put"})
     */
    public function udpatePassword(UserUpdatePassword $updateData, Usuario $usuario = null,EntityManagerInterface $em,UserPasswordHasherInterface $passwordHasher): JsonResponse
    {   
        $this->denyAccessUnlessGranted('ROLE_CHANGE_PASSWORD');
        if(!$usuario){
            return $this->json(['message' => 'Usuario no encontrado' , 'error' => null],404);
        }
        try {    
            $updateData->updatePassword($usuario,$this->getUser(),$em,$passwordHasher);
            $usuario->setEstado(new Activo());
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
     *  @OA\Response(
     *     response=200,
     *     description="Devuelve la información del usuario autenticado en el sistema",
     *     @Model(type=Usuario::class, groups={"user_simple_list","user_status_list","role_simple_list","tipodocumento_simple_list"})
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
            return $this->successResponse($this->getUser(),['user_simple_list','user_status_list','role_simple_list','tipodocumento_simple_list']);
        } catch (\Exception $e) {
            return $this->returnErrorResponse('Error al obtener la información','user', $e->getMessage(),500);
        }        
    }

    /**
     *  Devuelve la información del usuario enviado por parámetro
     *
     *  Devuelve la información del usuario enviado por parámetro
     * 
     *  @OA\Response(
     *     response=200,
     *     description="Devuelve la información del usuario enviado por parámetro",
     *     @Model(type=Usuario::class, groups={"user_simple_list","user_status_list","role_simple_list","tipodocumento_simple_list"})
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
            return $this->successResponse($usuario,['user_simple_list','user_status_list','role_simple_list','tipodocumento_simple_list']);
        } catch (\Exception $e) {
            return $this->returnErrorResponse('Error al obtener la información','user', $e->getMessage(),500);
        }        
    }
}
