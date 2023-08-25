<?php

namespace App\Controller\Security;

use App\Controller\BaseController;
use App\Entity\Security\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/roles")
 */
class RoleController extends BaseController
{
    /**
     * 
     * Listado de roles del sistema.
     *
     * Listado de roles del sistema
     * @OA\Response(
     *     response=200,
     *     description="Devuelve un listado de roles del sistema",
     * )
     * @OA\Tag(name="Roles")
     * @Security(name="Bearer")
     * 
     * @Route("", methods={"GET"}, name="role_list")
     */ 
    public function list(Request $request,EntityManagerInterface $em) : JsonResponse
    {
        return $this->successResponse($em->getRepository(Role::class)->findAll(),['role_simple_list']);
    }
}
