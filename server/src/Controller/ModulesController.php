<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Module;
use App\Entity\EventModule;
use App\Modules\ModulesManager;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ModulesController extends AbstractController
{

    /**
     * @Route("/api/eventModules/{id}/{method}", name="get_module_s_configuration", methods={"GET"})
     */
    public function getModuleConfiguration(ModulesManager $module_manager)
    {
        if ($module_manager->hasError())
            return $this->jsonResponse($this->fillMessage($module_manager->getError()->message), $module_manager->getError()->code);
            
        $module = $module_manager->getModule();
        return $this->htmlResponse($module->call());
    }

    /**
     * @Route("/api/eventModules/{id}/{method}", name="set_module_s_configuration", methods={"POST"})
     */
    public function setModuleConfiguration(ModulesManager $module_manager)
    {
        if ($module_manager->hasError())
            return $this->jsonResponse($this->fillMessage($module_manager->getError()->message), $module_manager->getError()->code);

        $module = $module_manager->getModule();
        return $this->htmlResponse($module->call(), $module->status);
    }

    /**
     * @Route("/api/addModuleToEvent", name="create_event_module", methods={"POST"})
     */
    public function createEventModule(Request $request)
    {
        $data = json_decode($request->getContent());

        if (!isset($data->id_event) || empty($data->id_event))
            return $this->jsonResponse($this->fillMessage("id_event est requis"), 400);

        if (!isset($data->id_module) || empty($data->id_module))
            return $this->jsonResponse($this->fillMessage("id_module est requis"), 400);

        try {
            // creating new user
            $event = $this->getDoctrine()
            ->getRepository(Event::class)
            ->find($data->id_event);

            $module = $this->getDoctrine()
            ->getRepository(Module::class)
            ->find($data->id_module);

            if (empty($event))
                return $this->jsonResponse($this->fillMessage("Événement introuvable"), 400);

            if (empty($module))
                return $this->jsonResponse($this->fillMessage("Module introuvable"), 400);

            $eventModule = new EventModule();
            $eventModule->setData("");
            $eventModule->setIdEvent($event);
            $eventModule->setIdModule($module);

            // adding it into database
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($eventModule);
            $manager->flush();
        } catch (Exception $e) {
            return $this->jsonResponse($this->fillMessage("Le module n'a n'a pas pu être ajouté à l'événement."), 400);
        }

        return $this->jsonResponse($this->fillMessage("Le module à été ajouté à l'événement"));
    }

    protected function fillMessage($text, $prefix = "message"){
        return [
            $prefix => $text
        ];
    }

    protected function jsonResponse($tpm_data, $code = 200) {
        $data = !is_array($tpm_data) ? ["message" => $tpm_data] : $tpm_data;
        $data['code'] = $code;

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->setCharset('UTF-8');
        $response->setStatusCode($code);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    protected function htmlResponse($data, $code = 200) {
        $response = new Response();
        $response->setContent($data);
        $response->setCharset('UTF-8');
        $response->setStatusCode($code);
        $response->headers->set('Content-Type', 'text/html');
        return $response;
    }
}
