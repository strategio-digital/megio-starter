<?php
declare(strict_types=1);

namespace App\User\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function login(): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/login.latte', [
            'title' => 'Přihlášení',
            'description' => 'Přihlaste se do svého účtu',
        ]);
    }

    public function register(): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/register.latte', [
            'title' => 'Registrace',
            'description' => 'Vytvořte si nový účet',
        ]);
    }
}
