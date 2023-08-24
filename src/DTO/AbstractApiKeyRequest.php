<?php 

namespace App\DTO;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractApiKeyRequest extends AbstractRequest
{
    private $params;
    
    public function __construct(ValidatorInterface $validator, ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->matches();
        parent::__construct($validator);
    }

    public function matches():bool
    {
        $request     = $this->getRequest();
        $allowed_ips = $this->params->get('trusted_ips');
        $env         =  $this->params->get('app_env');
     
        if(($env != 'dev') && (!in_array($request->getClientIp(),$allowed_ips))){
            throw new AccessDeniedException();
        }
        //valido que exista un header con la llave api_key
        if(!$apiKey = $request->headers->get('Bearer')){
             throw new AccessDeniedException();
        }

        //valido que sea una api_key válida
        if($apiKey != $this->params->get('api_key')){
             throw new AccessDeniedException();
        }    

        return true;
    }
}