<?php
declare(strict_types=1);

namespace App\Home\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Megio\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly Translator $translator,
    ) {}

    public function index(?string $locale = null): Response
    {
        if ($locale === $this->translator->getDefaultShortCodeFromEnv()) {
            return $this->redirect('home');
        }

        return $this->render(Path::viewDir() . '/home/controller/home.latte');
    }
}
