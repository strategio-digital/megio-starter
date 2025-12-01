<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Megio\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly Translator $translator,
    ) {}

    public function dashboard(): Response
    {
        return $this->render(Path::viewDir() . '/dashboard/controller/dashboard.latte', [
            'title' => $this->translator->translate('dashboard.page.title'),
            'description' => $this->translator->translate('dashboard.page.description'),
        ]);
    }
}
