<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use App\Entity\User;
use App\Entity\Video;

class UserController extends AbstractController
{

    private function resjson($data)
    {
        // Serializar datos con servicio de serializer
        $json = $this->get('serializer')->serialize($data, 'json');
        //Response con httpfoundation
        $response = new Response();
        //Asignar contenido a la respuesta
        $response->setContent($json);
        //Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');
        //Devolver la respuesta
        return $response;
    }

    public function index()
    {
        $user_repo = $this->getDoctrine()->getRepository(User::class);

        $video_repo = $this->getDoctrine()->getRepository(Video::class);

        $user = $user_repo->find(1);

        $videos = $video_repo->findAll();

        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',

        ];


        /*
    foreach ($users as $user) {
        echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";

        foreach ( $user->getVideos() as $video ) {
            echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
        }
    }
*/
        //  die();


        return $this->resjson($videos);
    }

    public function create(Request $request)
    {
        // Recoger los datos por post

        $json = $request->get('json', null);

        //Decodificar el json

        $params = json_decode($json);

        //Respuesta por defecto

        $data = [
            'status' => 'error',
            'code' => 200,
            'message' => 'El usuario no se ha creado'
        ];

        //Comprobar y validar datos

        if ($json != null) {
            $name = (!empty($params->name)) ? $params->name : null;
            $surname = (!empty($params->surname)) ? $params->surname : null;
            $email = (!empty($params->email)) ? $params->email : null;
            $password = (!empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if (!empty($email) && count($validate_email) == 0 && !empty($password) && !empty($surname) && !empty($name)) {
                //si la validación es correctar, crear el objeto del usuario
                $user = new User();
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \Datetime('now'));

                //Cifrar la contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                //Comprobar si el usuario existe (duplicados)
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email
                ));

                //Si no existe, guardar en BD  

                if (count($isset_user) == 0) {
                    //inserto el usuario

                    $em->persist($user);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El usuario se ha creado',
                        'user' => $user
                    ];

                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'El usuario ya existe'
                    ];
                }
            }
        }

        //Respuesta en JSON

        return new JsonResponse($data);
    }
}
