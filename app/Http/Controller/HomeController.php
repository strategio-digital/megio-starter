<?php
declare(strict_types=1);

namespace App\Http\Controller;

use Saas\Helper\Path;
use Saas\Http\Controller\Base\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->render(Path::viewDir() . '/controller/home.latte', [
            'title' => 'Megio Panel',
            'description' => 'Most powerful tool for creating webs, apps & APIs.'
        ]);
    }
}