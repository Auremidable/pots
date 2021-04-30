<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;use Symfony\Component\Dotenv\Dotenv;

class AuthenticationController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, \Swift_Mailer $mailer)
    {
        $data = json_decode($request->getContent());


        if (!isset($data->username) || empty($data->username))
            return $this->jsonResponse($this->fillMessage("Un nom d'utilisateur est requis"), 400);

        if (!isset($data->email) || empty($data->email))
            return $this->jsonResponse($this->fillMessage("Une adresse email est requise"), 400);

        if (!isset($data->password) || empty($data->password))
            return $this->jsonResponse($this->fillMessage("Un mot de passe est requis"), 400);

        try {
            $this->createUser($data);
        } catch (Exception $e) {
            return $this->jsonResponse($this->fillMessage("L'utilisateur n'a pas pu être créé."), 400);
        }

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $data->email]);
        
        $message = (new \Swift_Message("Bienvenue chez Pot's"))
            ->setFrom('epitestaurelie@gmail.com')
            ->setTo($data->email)
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/registration.html.twig',
                    ['id' => $user->getId()]
                ),
                'text/html'
            );
            $mailer->send($message);


        return $this->jsonResponse($this->fillMessage("L'utilisateur à été créé."));
    }


    /**
     * @Route("/is_username_available", name="is_username_available", methods={"GET"})
     */
    public function is_username_available(Request $request)
    {
        $username= $request->query->get('username');


        if (empty($username))
            return $this->jsonResponse($this->fillMessage("Un nom d'utilisateur est requis"), 400);

        return $this->jsonResponse($this->fillMessage($this->isUserNameAvailable($username)));
    }


    /**
     * @Route("/is_email_available", name="is_email_available", methods={"GET"})
     */
    public function is_email_available(Request $request)
    {
        $email= $request->query->get('email');


        if (empty($email))
            return $this->jsonResponse($this->fillMessage("Un email est requis"), 400);

        return $this->jsonResponse($this->fillMessage($this->isEmailAvailable($email)));
    }

    /**
     * @Route("/confirm/{id}", name="confirm_email")
     */
    public function confirmEmail($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (isset($user) || !empty($user))  {
            $user->setActive(true);
            $entityManager->flush();
        }

        return $this->redirect('http://localhost/');
    }


    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request)
    {
        $data = json_decode($request->getContent());

        if (!isset($data->email) || empty($data->email))
            return $this->jsonResponse($this->fillMessage("Une adresse email est requise"), 400);

        if (!isset($data->password) || empty($data->password))
            return $this->jsonResponse($this->fillMessage("Un mot de passe est requis"), 400);

        
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $data->email]);

        if (empty($user))
            return $this->jsonResponse($this->fillMessage("Les informations de connexion sont incorrectes."), 401);

        if (!$user->getActive())
            return $this->jsonResponse($this->fillMessage("Veuillez confirmer votre adresse email via l'email de confirmation."), 401);

        if (password_verify($data->password, $user->getPassword())) {
            // return token
            $data = array(
                'token' => $this->createToken($user),
                'user' => array(
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'picture' => $user->getPicture()
                )
            );
            return $this->jsonResponse($data);
        } else {
            return $this->jsonResponse($this->fillMessage("Les informations de connexion sont incorrectes."), 401);
        }
    }

    protected function createToken($user) {
        $secret = $_SERVER['SECRET_KEY_JWT'];

        if (!($user instanceof User))
            return false;

        $data = [
            "id" => $user->getId(),
            "username" => $user->getName(),
            "email" => $user->getEmail(),
            "roles" => $user->getRoles(),
        ];

        // JWT header
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // JWT Payload
        $payload = json_encode($data);
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // JWT Signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // JWT
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }


    protected function isUserNameAvailable($username) {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(['name' => $username]);

            return count($user) < 1;

    }

    protected function isEmailAvailable($email) {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy(['email' => $email]);

            return count($user) < 1;
    }

    protected function createUser($data) {
        $roles[] = 'ROLE_USER';

        // creating new user
        $user = new User();
        $user->setName($data->username);
        $user->setRoles($roles);
        $user->setEmail($data->email);
        $user->setActive(false);
        $user->setPassword(password_hash($data->password, PASSWORD_DEFAULT));

        // adding it into database
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();
    }

    protected function fillMessage($text, $prefix = "message"){
        return [
            $prefix => $text
        ];
    }

    protected function jsonResponse($data = [], $code = 200) {
        $data['code'] = $code;

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->setCharset('UTF-8');
        $response->setStatusCode($code);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
