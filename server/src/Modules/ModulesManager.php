<?php

namespace App\Modules;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Module;
use App\Entity\EventModule;
use Doctrine\ORM\EntityManager;
use App\Repository\ModuleRepository;
use App\Security\TokenAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ModulesManager {

    protected $em;
    protected $module;
    protected $error = false;

    public function __construct(EntityManager $em, RequestStack $requestStack) {
        $this->em = $em;

        $request = $requestStack->getCurrentRequest();
        $id_event_module = $request->attributes->get('id');
        $method = $request->attributes->get("method");

        $eventModule = $this->em->getRepository(EventModule::class)->find($id_event_module);
        if (!$eventModule)
            return $this->error = ['message' => 'Module not found', 'code' => 404];
        
        $tokenAuth = new TokenAuthenticator($em);
        $emailAsker = $tokenAuth->getCredentials($request);
        $emailCreator = $eventModule->getIdEvent()->getCreator()->getEmail();

        $emails_auth = array($emailCreator);

        $guests = $eventModule->getIdEvent()->getGuests();
        foreach($guests->toArray() as $guest_info) {
            $emails_auth[] = $guest_info->getUser()->getEmail();
        }

        if (!in_array($emailAsker, $emails_auth))
            return $this->error = ['message' => 'Vous n\'avez pas accès à ce module', 'code' => 401];

        $modulename =$eventModule->getIdModule()->getName();
        $classname = '\App\Modules\\' . $modulename . 'Module';

        if (!class_exists($classname))
            return $this->error = ['message' => 'Module not found', 'code' => 404];

        $prefix = $request->getMethod() === "GET" ? "get" : "set";
        $this->method = $prefix . ucfirst($method);
        if (!method_exists($classname, $this->method))
            return $this->error = ['message' => 'Method not found', 'code' => 404];
            
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $emailAsker]);
        $this->module = new $classname($this->em, $request, $this->method, $eventModule, $user);
    }

    public function hasError() {
        return $this->error !== false;
    }

    public function getError() {
        return (object) $this->error;
    }

    public function getModule() {
        return $this->module;
    }
}