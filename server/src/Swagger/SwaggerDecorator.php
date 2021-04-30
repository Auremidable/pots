<?php

declare(strict_types=1);

namespace App\Swagger;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);
        $docs = $this->setSchemas($docs);
        $docs = $this->getAuthenticationDocumentation($docs);
        $docs = $this->getEventDocumentation($docs);
        $docs = $this->getModuleDocumentation($docs);
        $docs = $this->getContactDocumentation($docs);

        return $docs;
    }

    private function setSchemas($docs): array 
    {

        $docs['components']['schemas']['Credentials'] = [
            'type' => 'object',
            'properties' => [
                'email' => ['type' => 'string', 'example' => 'marie@pots.fr'],
                'password' => ['type' => 'string', 'example' => 'marie'],
            ]
        ];

        $docs['components']['schemas']['Token'] = [
            'type' => 'object',
            'properties' => [
                'token' => ['type' => 'string', 'readOnly' => true],
                'user' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer', 'readOnly' => true],
                        'username' => ['type' => 'string', 'readOnly' => true],
                        'email' => ['type' => 'string', 'readOnly' => true],
                        'picture' => ['type' => 'string', 'readOnly' => true]
                    ],
                    'readOnly' => true,
                ],
                'code' => ['type' => 'integer', 'readOnly' => true, 'example' => 200]
            ]
        ];

        $docs['components']['schemas']['API response'] = [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string'],
                'code' => ['type' => 'integer', 'example' => 200]
            ]
        ];

        $docs['components']['schemas']['API response Bool'] = [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'boolean'],
                'code' => ['type' => 'integer', 'example' => 200]
            ]
        ];

        return $docs;
    }

    private function getAuthenticationDocumentation($docs): array
    {

        /* /login */
        $docs['paths']['/login']['post']['tags'] = ["Authentication"];
        $docs['paths']['/login']['post']['summary'] = 'Get JWT token to login.';
        $docs['paths']['/login']['post']['requestBody']['description'] = 'Create new JWT Token';
        $docs['paths']['/login']['post']['requestBody']['content']['application/json']['schema']['$ref'] = '#/components/schemas/Token';
        $docs['paths']['/login']['post']['responses'][200]['description'] = 'Get JWT token';
        $docs['paths']['/login']['post']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/Authentication';


        /* /register */
        $register_parameters = [
            'type' => 'object',
            'properties' => [
                'email' => ['type' => 'string', 'example' => 'marie@pots.fr'],
                'password' => ['type' => 'string', 'example' => 'marie'],
                'username' => ['type' => 'string', 'example' => 'Marie Dupond'],
            ]
        ];

        $docs['paths']['/register']['post']['tags'] = ["Authentication"];
        $docs['paths']['/register']['post']['summary'] = 'Add user into database';
        $docs['paths']['/register']['post']['requestBody']['description'] = 'User\'s informations';
        $docs['paths']['/register']['post']['requestBody']['content']['application/json']['schema'] = $register_parameters;
        $docs['paths']['/register']['post']['responses'][200]['description'] = 'Success';
        $docs['paths']['/register']['post']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response';


        /* /is_username_available */
        $docs['paths']['/is_username_available']['get']['tags'] = ["Authentication"];
        $docs['paths']['/is_username_available']['get']['summary'] = 'Looking if username is available';
        $docs['paths']['/is_username_available']['get']['parameters'] = [
            ["name" => "username", "in" => "query", "required" => true, "schema" => ["type" => "string"]]
        ];
        $docs['paths']['/is_username_available']['get']['responses'][200]['description'] = 'Is username available';
        $docs['paths']['/is_username_available']['get']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response Bool';


        /* /is_email_available */
        $docs['paths']['/is_email_available']['get']['tags'] = ["Authentication"];
        $docs['paths']['/is_email_available']['get']['summary'] = 'Looking if email is available';
        $docs['paths']['/is_email_available']['get']['parameters'] = [
            ["name" => "email", "in" => "query", "required" => true, "schema" => ["type" => "string"]]
        ];
        $docs['paths']['/is_email_available']['get']['responses'][200]['description'] = 'Is email available';
        $docs['paths']['/is_email_available']['get']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response Bool';


        return $docs;
    }

    private function getEventDocumentation($docs): array
    {

        /* /api/myevents */
        $docs['paths']['/api/myevents']['get']['tags'] = ["Event"];
        $docs['paths']['/api/myevents']['get']['summary'] = 'Return all user\'s events';
        $docs['paths']['/api/myevents']['get']['responses'][200]['description'] = 'User\'s events';
        $docs['paths']['/api/myevents']['get']['responses'][200]['content']['application/json']['schema'] = [
            'type' => 'object',
            'properties' => [
                'events' => [
                    'type' => 'object',
                    'properties' => [
                        'created' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'banner' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'date' => ['type' => 'string'],
                                    'location' => ['type' => 'string'],
                                    'creator' => ['type' => 'string'],
                                    'modules' => ['type' => 'string'],
                                    'guests' => ['type' => 'string'],
                                ]
                            ]
                        ],
                        'invited' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'banner' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'date' => ['type' => 'string'],
                                    'location' => ['type' => 'string'],
                                    'creator' => ['type' => 'string'],
                                    'modules' => ['type' => 'string'],
                                    'guests' => ['type' => 'string'],
                                ]
                            ]
                        ]
                    ]
                ],
                'code' => ['type' => 'integer', 'example' => 200]
            ]
        ];

        return $docs;
    }

    private function getModuleDocumentation($docs): array
    {

        /* /api/addModuleToEvent */
        $docs['paths']['/api/addModuleToEvent']['post']['tags'] = ["Module"];
        $docs['paths']['/api/addModuleToEvent']['post']['summary'] = 'Add Module to an event';
        $docs['paths']['/api/addModuleToEvent']['post']['requestBody']['description'] = 'Add module to an event';
        $docs['paths']['/api/addModuleToEvent']['post']['requestBody']['content']['application/json']['schema'] = [
            'type' => 'object',
            'properties' => [
                'id_event' => ['type' => 'integer'],
                'id_module' => ['type' => 'integer']
            ]
        ];
        $docs['paths']['/api/addModuleToEvent']['post']['responses'][200]['description'] = 'Success';
        $docs['paths']['/api/addModuleToEvent']['post']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response';

        /* /api/eventModules/{id}/{method} */
        // POST
        $docs['paths']['/api/eventModules/{id}/{method}']['post']['tags'] = ["Module"];
        $docs['paths']['/api/eventModules/{id}/{method}']['post']['summary'] = 'Call module\'s method';
        $docs['paths']['/api/eventModules/{id}/{method}']['post']['requestBody']['description'] = 'The data to pass to the method';
        $docs['paths']['/api/eventModules/{id}/{method}']['post']['requestBody']['content']['application/json']['schema'] = ['type' => 'object'];
        $docs['paths']['/api/eventModules/{id}/{method}']['post']['responses'][200]['description'] = 'The data returned by the method';
        $docs['paths']['/api/eventModules/{id}/{method}']['post']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response';
        // GET
        $docs['paths']['/api/eventModules/{id}/{method}']['get']['tags'] = ["Module"];
        $docs['paths']['/api/eventModules/{id}/{method}']['get']['summary'] = 'Get module page';
        $docs['paths']['/api/eventModules/{id}/{method}']['get']['responses'][200]['description'] = 'The HTML asked for';
        $docs['paths']['/api/eventModules/{id}/{method}']['get']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response';

        return $docs;
    }

    private function getContactDocumentation($docs): array
    {
        /* /api/askForFriend/{id_friend} */
        $docs['paths']['/api/askForFriend/{id_friend}']['post']['tags'] = ["Contact"];
        $docs['paths']['/api/askForFriend/{id_friend}']['post']['summary'] = 'Send a friend request';
        $docs['paths']['/api/askForFriend/{id_friend}']['post']['requestBody']['description'] = 'Send a friend request';
        $docs['paths']['/api/askForFriend/{id_friend}']['post']['responses'][200]['description'] = 'Success';
        $docs['paths']['/api/askForFriend/{id_friend}']['post']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response';

        /* /api/friendsRequest */
        // GET
        $docs['paths']['/api/friendsRequest']['get']['tags'] = ["Contact"];
        $docs['paths']['/api/friendsRequest']['get']['summary'] = 'Get all friend requests';
        $docs['paths']['/api/friendsRequest']['get']['responses'][200]['description'] = 'All friend requests';
        $docs['paths']['/api/friendsRequest']['get']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response';

        /* /api/denyFriendsRequest/{id_asker} */
        $docs['paths']['/api/denyFriendsRequest/{id_asker}']['post']['tags'] = ["Contact"];
        $docs['paths']['/api/denyFriendsRequest/{id_asker}']['post']['summary'] = 'Deny friend request';
        $docs['paths']['/api/denyFriendsRequest/{id_asker}']['post']['requestBody']['description'] = 'Deny friend request';
        $docs['paths']['/api/denyFriendsRequest/{id_asker}']['post']['responses'][200]['description'] = 'Success';
        $docs['paths']['/api/denyFriendsRequest/{id_asker}']['post']['responses'][200]['content']['application/json']['schema']['$ref'] = '#/components/schemas/API response';

        return $docs;
    }
}