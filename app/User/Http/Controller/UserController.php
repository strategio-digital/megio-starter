<?php
declare(strict_types=1);

namespace App\User\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends Controller
{
    public function login(): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/login.latte', [
            'title' => 'Login',
            'description' => 'Sign in to your account',
        ]);
    }

    public function register(): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/register.latte', [
            'title' => 'Registration',
            'description' => 'Create a new account',
        ]);
    }

    public function activate(string $token): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/activate.latte', [
            'title' => 'Account Activation',
            'description' => 'Activate your account',
            'token' => $token,
        ]);
    }
}
