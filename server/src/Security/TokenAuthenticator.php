<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TokenAuthenticator extends AbstractGuardAuthenticator {
    
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function supports(Request $request){
        return true;
    }

    public function getCredentials(Request $request) {
        // must return $credentials
        $token = $request->headers->get("Authorization");
        if (empty($token))
            return false;

        $decoded_token = $this->decodeToken($token);

        if (!$decoded_token)
            return false;

        return $decoded_token->email;
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        if ($credentials === null)
            return null;
        
        return $userProvider->loadUserByUsername($credentials);
        
    }

    public function checkCredentials($credentials, UserInterface $user) {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $data = [
            'message' => "Authentication failed."
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $exception = null) {
        $data = [
            'message' => 'Authentication Required :)',
            'data' => $exception
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe() {
        return false;
    }

    protected function decodeToken($token) {
        if (!$this->isValid($token))
            return false;
        list($enc_header, $enc_payload, $enc_signature) = explode(".", $token);
        return json_decode(base64_decode(str_replace(["-", "_"], ["+", "/"], $enc_payload)));
    }

    protected function isValid($token) {
        $secret = $_SERVER['SECRET_KEY_JWT'];
        list($enc_header, $enc_payload, $enc_signature) = explode(".", $token);
        $token_signature = hash_hmac('sha256', $enc_header . "." . $enc_payload, $secret, true);
        $base64UrlJwt_Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($token_signature));

        return $base64UrlJwt_Signature === $enc_signature;
    }
}