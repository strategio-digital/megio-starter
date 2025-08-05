<?php
declare(strict_types=1);

namespace App\Home\Http\Controller;

use Megio\Helper\Path;
use Megio\Http\Controller\Base\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->render(Path::viewDir() . '/home/controller/home.latte', [
            'title' => 'Megio Panel',
            'description' => 'Most powerful tool for creating webs, apps & APIs.'
        ]);
    }
}