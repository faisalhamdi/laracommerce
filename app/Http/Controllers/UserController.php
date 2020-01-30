<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    public function index()
    {
        return $this->user->getPaginate(10);
    }

    public function show($params)
    {
        if (!is_numeric($params)) {
            return $this->user->findBy('email', $params);
        }

        return $this->user->find($params);
    }
}
