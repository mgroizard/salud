<?php
// src/EventSubscriber/ExceptionSubscriber.php
namespace App\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessEventListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $logger;
    protected $managerRegistry;

    public function setContainer(ContainerInterface $container = null, LoggerInterface $logger = null,ManagerRegistry $mr = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->managerRegistry = $mr;
    }

    public static function getSubscribedEvents(): array
    {
        // return the subscribed events, their methods and priorities
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        try{
            $user = $event->getUser();
            $user->setLastLogin();
            $this->managerRegistry->getManager()->flush();   
        }catch(\Exception $e){
            $this->logger->error('Error updating last login datetime for the user with id: ' . $user->getId());
        }

    }
}
