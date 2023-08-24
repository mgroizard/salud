<?php

namespace App\Controller;

use App\Entity\Security\Usuario;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class BaseController extends AbstractController
{
    protected $messages = ['message' => 'validation_failed', 'errors' => []];

    public function returnErrorResponse($message,$property,$value,$code = 400)
    {
        $messages = ['message' => 'validation_failed','errors' => [
            'property' => $property,
            'value' => $value,
            'message' => $message,
        ]];
        return $this->json($messages,$code);
    }

    public function successResponse($data,$context)
    {
        // $defaultContext = [
        //     AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
        //         return $object->getNombre();
        //     },
        // ];
        //  $normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];
        //  $serializer = new Serializer($normalizers, [new JsonEncoder()]);

       return $this->json($data,200,[],['groups' => $context]); 
   

        
    }
}
