<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Module;
use App\Entity\Contact;
use App\Entity\EventModule;
use App\Modules\ModulesManager;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ContactController extends AbstractController
{
    /**
     * @Route("/api/askForFriend/{id_friend}", name="ask_for_friend", methods={"POST"})
     */
    public function askForFriend(Request $request, $id_friend)
    {
        
        $token = $request->headers->get("Authorization");
        $loggedUserCache = $this->decodeToken($token);
        $loggedUser = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($loggedUserCache->id);
        
        if ((int)$loggedUser->getId() === (int)$id_friend)
            return $this->jsonResponse($this->fillMessage('Impossible de se demander soi-même en ami.'), 400);

        $friend = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id_friend);

        if (is_null($friend))
            return $this->jsonResponse($this->fillMessage('Impossible de trouver cet utilisateur.'), 400);

        $relation_user_friend = $this->getDoctrine()
        ->getRepository(Contact::class)
        ->findOneBy([
            'user1' => $loggedUser,
            'user2' => $friend,
        ]);

        $todo;
        switch ($this->areAlreadyFriends($loggedUser, $friend)) {
            case 'not_friend':
                $todo = "create";
                break;
            case 'already_asked_by_other':
                $todo = "update";
                break;
            case 'already_asked':
                return $this->jsonResponse($this->fillMessage('Vous avez déjà envoyé une demande d\'ami.'), 400);
            case 'already_friends':
                return $this->jsonResponse($this->fillMessage('Vous êtes déjà amis.'), 400);
            default:
                return $this->jsonResponse($this->fillMessage('Une erreur est survenue'), 500);
        }

        $manager = $this->getDoctrine()->getManager();

        if ($todo === "create") {
            $contact = new Contact();
            $contact->setUser1($loggedUser);
            $contact->setUser2($friend);
            $contact->setStatus(0);
            // adding it into database
            $manager->persist($contact);
            $manager->flush();

            return $this->jsonResponse($this->fillMessage("La demande à été correctement envoyée."));
        } else {
            $relation_friend_user = $this->getDoctrine()
                ->getRepository(Contact::class)
                ->findOneBy([
                    'user1' => $id_friend,
                    'user2' => $loggedUser->getId(),
                ]);
            $relation_friend_user->setStatus(1);
            $manager->persist($relation_friend_user);
            $manager->flush();

            return $this->jsonResponse($this->fillMessage("Vous avez accepté la demande d'ami."));
        }
    }

    
    /**
     * @Route("/api/friendsRequest", name="friends_request", methods={"GET"})
     */
    public function friendsRequest(Request $request)
    {
        $token = $request->headers->get("Authorization");
        $loggedUserCache = $this->decodeToken($token);
        $loggedUser = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($loggedUserCache->id);

        $friends_requests = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->findBy([
                'user2' => $loggedUser,
            ]);

        if (count($friends_requests) < 1)
            return $this->jsonResponse(["friendsRequest" => []]);

        $requests = array();
        foreach($friends_requests as $request) {
            $user = $request->getUser2();
            $requests[] = array(
                'id' => $user->getId(),
                'name' => $user->getUsername(),
                'picture' => $user->getPicture()
            );
        }

        return $this->jsonResponse(['friendsRequest' => $requests]);
    }

    /**
     * @Route("/api/denyFriendsRequest/{id_asker}", name="deny_friends_request", methods={"POST"})
     */
    public function denyFriendsRequest(Request $request, $id_asker)
    {
        $token = $request->headers->get("Authorization");
        $loggedUserCache = $this->decodeToken($token);
        $loggedUser = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($loggedUserCache->id);
        
        if ((int)$loggedUser->getId() === (int)$id_asker)
            return $this->jsonResponse($this->fillMessage('Opération impossible.'), 400);

        $asker = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id_asker);

        if (is_null($asker))
            return $this->jsonResponse($this->fillMessage('Impossible de trouver cet utilisateur.'), 400);

        $relation_user_asker = $this->getDoctrine()
        ->getRepository(Contact::class)
        ->findOneBy([
            'user1' => $asker,
            'user2' => $loggedUser,
        ]);

        switch ($this->areAlreadyFriends($loggedUser, $asker)) {
            case 'not_friend':
                return $this->jsonResponse($this->fillMessage('Cette demande n\'existe pas.'), 400);
            case 'already_friends':
            case 'already_asked_by_other':
            case 'already_asked':
                break;
            default:
                return $this->jsonResponse($this->fillMessage('Une erreur est survenue'), 500);
        }

        $id_contact = $this->getRelation($loggedUser, $asker);

        if (is_null($id_contact))
            return $this->jsonResponse($this->fillMessage('Relation non trouvée.'), 500);

        $contact = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->find($id_contact);
        
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($contact);
        $manager->flush();

        return $this->jsonResponse($this->fillMessage('Opération réussie.'));

    }

    protected function areAlreadyFriends($user, $friend) {
        list($relation_user_friend, $relation_friend_user) = $this->getRelations($user, $friend);

        if (is_null($relation_user_friend) && is_null($relation_friend_user))
                return 'not_friend';
        elseif (is_null($relation_user_friend)) {
            if ((int)$relation_friend_user->getStatus() === 0)
               return 'already_asked_by_other';
            return 'already_friends';
        }
        if ((int)$relation_user_friend->getStatus() === 0)
            return 'already_asked';
        return 'already_friends';
    }

    protected function getRelation($user, $friend) {
        list($relation_user_friend, $relation_friend_user) = $this->getRelations($user, $friend);
        
        if (is_null($relation_user_friend) && is_null($relation_friend_user))
                return false;
        elseif (is_null($relation_user_friend)) {
            return (int)$relation_friend_user->getId();
        }
        return (int)$relation_user_friend->getId();
    }

    protected function getRelations($user, $friend) {
        $relation_user_friend = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->findOneBy([
                'user1' => $user,
                'user2' => $friend,
            ]);

        $relation_friend_user = $this->getDoctrine()
            ->getRepository(Contact::class)
            ->findOneBy([
                'user1' => $friend,
                'user2' => $user,
            ]);

        return array($relation_user_friend, $relation_friend_user);
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
