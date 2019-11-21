<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;

class JwtAuth
{

    public $manager;
    public $key;

    public function __construct($manager)
    {
        $this->manager = $manager;
        $this->key = 'Hola_que_tal_este_es_el_master_full_stack12345678';
    }
    public function signup($email, $password, $gettoken = null)
    {
        //Comprobar si el usuario existe

        $user = $this->manager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'password' => $password
        ]);

        $siginup = false;
        if (is_object($user)) {
            $siginup = true;
        }

        // Si existe, generar el token de JWT 
        if ($siginup) {
            $token = [
                'sub' => $user->getId(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'email' => $user->getEmail(),
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            ];

            //Comprobar el flag gettoken, condicional
            $jwt = JWT::encode($token, $this->key, 'HS256');
            if (!empty($gettoken)) {
                $data = $jwt;
            } else {
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
                $data = $decoded;
            }
        } else {
            $data = [
                'status' => 'Error',
                'message' => 'Login failed'
            ];
        }

        //Devolver datos

        return $data;
    }

    public function checkToken($jwt, $identity = false)
    {
        $auth = false;

        try {
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }
        if (isset($decoded) && !empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($identity != false) {
            return $decoded;
        } else {
            return $auth;
        }
    }
}
