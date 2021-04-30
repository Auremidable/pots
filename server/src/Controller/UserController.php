<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Guest;
use App\Entity\Module;
use App\Entity\EventModule;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    /**
     * @Route("/api/myevents", name="get_users_s_events", methods={"GET"})
     */
    public function getModuleConfiguration(Request $request)
    {
        $token = $request->headers->get("Authorization");
        $loggedUser = $this->decodeToken($token);

        $created_events = $this->getDoctrine()
        ->getRepository(Event::class)
        ->findBy(['creator' => $loggedUser->id]);
        $created_events_serialized = $this->container->get('serializer')->serialize($created_events, 'json');

        $guests = $this->getDoctrine()
        ->getRepository(Guest::class)
        ->findBy(['user' => $loggedUser->id]);
        $invited_events = array();
        foreach($guests as $guest) {
            $invited_events[] = $this->getDoctrine()
            ->getRepository(Event::class)
            ->find($guest->getEvent()->getId());
        }
        $invited_events_serialized = $this->container->get('serializer')->serialize($invited_events, 'json');


        $events = array(
            "created" => json_decode($created_events_serialized, true),
            "invited" => json_decode($invited_events_serialized, true)
        );

        return $this->jsonResponse($this->fillMessage($events, 'events'));
    }

    protected function decodeToken($token) {
        list($enc_header, $enc_payload, $enc_signature) = explode(".", $token);
        return json_decode(base64_decode(str_replace(["-", "_"], ["+", "/"], $enc_payload)));
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
}
