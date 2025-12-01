<?php
declare(strict_types=1);

namespace App\User\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Megio\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends Controller
{
    public function __construct(
        private readonly Translator $translator,
    ) {}

    public function login(): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/login.latte', [
            'title' => $this->translator->translate('user.page.login.title'),
            'description' => $this->translator->translate('user.page.login.description'),
        ]);
    }

    public function register(): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/register.latte', [
            'title' => $this->translator->translate('user.page.register.title'),
            'description' => $this->translator->translate('user.page.register.description'),
        ]);
    }

    public function activate(string $token): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/activate.latte', [
            'title' => $this->translator->translate('user.page.activation.title'),
            'description' => $this->translator->translate('user.page.activation.description'),
            'token' => $token,
        ]);
    }

    public function forgotPassword(): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/forgot-password.latte', [
            'title' => $this->translator->translate('user.page.forgot_password.title'),
            'description' => $this->translator->translate('user.page.forgot_password.description'),
        ]);
    }

    public function resetPassword(string $token): Response
    {
        return $this->render(Path::viewDir() . '/user/controller/reset-password.latte', [
            'title' => $this->translator->translate('user.page.reset_password.title'),
            'description' => $this->translator->translate('user.page.reset_password.description'),
            'token' => $token,
        ]);
    }
}
