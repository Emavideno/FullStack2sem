<?php

namespace App\Controllers;

use App\Route;
use App\AbstractController;
use Psr\Http\Message\ServerRequestInterface;

class HomeController extends AbstractController
{
    #[Route('/', 'GET')]
    public function index(ServerRequestInterface $request): void
    {
        $this->render('index');
    }

    #[Route('/random', 'GET')]
    public function random(ServerRequestInterface $request): void
    {
        $types = [
            'flag_to_country',
            'country_to_flag',
            'capital_to_country',
            'country_to_capital',
            'population',
            'area'
        ];
        $randomType = $types[array_rand($types)];
        $this->redirect("/quiz/play?type={$randomType}");
    }
}
