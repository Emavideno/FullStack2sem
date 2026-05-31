<?php

namespace App\Controllers;

use App\Route;
use App\AbstractController;
use App\Models\User;
use App\Logger\LoggerFactory;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends AbstractController
{
    #[Route('/register', 'GET')]
    public function showRegister(ServerRequestInterface $request): void
    {
        $this->render('auth/register');
    }

    #[Route('/register', 'POST')]
    public function register(ServerRequestInterface $request): void
    {
        try {
            $postData = $this->getPostData($request);
            $login = trim($postData['login'] ?? '');
            $password = $postData['password'] ?? '';

            $validator = new \App\Validation\Validator();
            $validator
                ->validateNotEmpty('login', $login, 'Введите логин')
                ->validateMinLength('login', $login, 3, 'Логин должен быть не менее 3 символов')
                ->validateNotEmpty('password', $password, 'Введите пароль')
                ->validateMinLength('password', $password, 3, 'Пароль должен быть не менее 3 символов')
                ->validateUnique('login', $login, function ($login) {
                    return User::findByLogin($login) !== null;
                }, 'Пользователь с таким логином уже существует');

            if (!$validator->isValid()) {
                $this->render('auth/register', ['error' => $validator->getFirstError()]);
                return;
            }

            $user = new User($login, $password);
            if ($user->save()) {
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_login'] = $user->getLogin();
                $_SESSION['user_role'] = $user->getRole();
                $this->redirect('/');
            }
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Registration error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->render('auth/register', ['error' => 'Ошибка регистрации']);
        }
    }

    #[Route('/login', 'GET')]
    public function showLogin(ServerRequestInterface $request): void
    {
        $this->render('auth/login');
    }

    #[Route('/login', 'POST')]
    public function login(ServerRequestInterface $request): void
    {
        try {
            $postData = $this->getPostData($request);
            $login = trim($postData['login'] ?? '');
            $password = $postData['password'] ?? '';

            $validator = new \App\Validation\Validator();
            $validator
                ->validateNotEmpty('login', $login, 'Введите логин')
                ->validateNotEmpty('password', $password, 'Введите пароль');

            if (!$validator->isValid()) {
                $this->render('auth/login', ['error' => $validator->getFirstError()]);
                return;
            }

            $user = User::findByLogin($login);

            if (!$user || !$user->verifyPassword($password)) {
                $this->render('auth/login', ['error' => 'Неверный логин или пароль']);
                return;
            }

            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_login'] = $user->getLogin();
            $_SESSION['user_role'] = $user->getRole();

            $this->redirect('/');
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Login error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->render('auth/login', ['error' => 'Ошибка входа']);
        }
    }

    #[Route('/logout', 'GET')]
    public function logout(ServerRequestInterface $request): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}
