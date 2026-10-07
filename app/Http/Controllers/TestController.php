<?php

namespace App\Http\Controllers;

use Inertia\ResponseFactory;

class TestController extends Controller
{
    public function __construct(private ResponseFactory $inertia) {}

    public function test() {
        return $this->inertia->render('Test', [
            'message' => 'Hello World!',
        ]);
    }
}
