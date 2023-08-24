<?php

namespace App\EventListener;

use App\Entity\Security\Log;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Doctrine\Bundle\DoctrineBundle\Registry;

class ActivityListener
{
    protected $tokenStorage;
    protected $logger;
    protected $doctrine;

    public function init(UsageTrackingTokenStorage $tokenStorage = null, LoggerInterface $logger = null, Registry $doctrine)
    {
        $this->tokenStorage = $tokenStorage;
        $this->logger       = $logger;
        $this->doctrine     = $doctrine;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $user = null;
        
        if($this->tokenStorage && $this->tokenStorage->getToken()){
            $user = $this->tokenStorage->getToken()->getUser();
        }
        
        if ($user) {
            $request = $event->getRequest();
            $route = $request->get('_route');
        
            if (Log::isRouteLoggable($route)) 
            {    
           
                $em = $this->doctrine->getManager();
                $log = new Log(
                    $user,
                    $request->getClientIp(),
                    $route,
                    $request->getMethod()
                );
                
                $datos = [];
                
                if($data = json_decode($request->getContent(),true))
                {
                    foreach ($data as $key => $value) {
                        if($key == 'password'){
                             $data[$key] = '**********';
                        }
                    }
                    foreach ($data as $key => $value) {
                        if (!is_array($value)) {
                            $nuevo_dato = $value;                    
                        } else {
                            $nuevo_dato = json_encode($value);
                        }

                        $datos[$key] = $nuevo_dato;
                    }
                }

                $log->setDescripcion(
                    json_encode([
                        'datos' => $datos,
                        'params' => $request->get('_route_params'),
                    ])
                );
                try {
                    $em->persist($log);
                    $em->flush();
                } catch (\Exception $e) {
                    $this->logger->error('Error al registrar un log ' . $e->getMessage());
                }
            }
            return;
        }
    }
}
