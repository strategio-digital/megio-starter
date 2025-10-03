<?php
declare(strict_types=1);

namespace App\Dashboard\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function dashboard(): Response
    {
        return $this->render(Path::viewDir() . '/dashboard/controller/dashboard.latte', [
            'title' => 'Dashboard',
            'description' => 'Přehled vašich projektů',
        ]);
    }
}
