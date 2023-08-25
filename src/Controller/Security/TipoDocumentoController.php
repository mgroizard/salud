<?php

namespace App\Controller\Security;

use App\Controller\BaseController;
use App\Entity\Security\Role;
use App\Entity\Security\TipoDocumento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/tipo_documentos")
 */
class TipoDocumentoController extends BaseController
{
    /**
     * 
     * Listado de los tipos de documentos del sistema.
     *
     * Listado de los tipos de documentos del sistema
     * @OA\Response(
     *     response=200,
     *     description="Devuelve un listado de los tipos de documentos del sistema",
     * )
     * @OA\Tag(name="Tipos de Documentos")
     * @Security(name="Bearer")
     * 
     * @Route("", methods={"GET"}, name="tipo_documento_list")
     */ 
    public function list(Request $request,EntityManagerInterface $em) : JsonResponse
    {
        return $this->successResponse($em->getRepository(TipoDocumento::class)->findAll(),['tipodocumento_simple_list']);
    }
}
