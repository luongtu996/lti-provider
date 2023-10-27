<?php

namespace App\Http\Controllers;

use App\Lti\Lti13Cookie;
use App\Services\Lti13Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CookierController extends Controller
{
    public $lti13Service;
    public $cookie;

    /**
     * Create a new controller instance.
     *
     * HomeController constructor.
     */
    public function __construct(
        Lti13Service $lti13Service,
        Lti13Cookie $cookie
    )
    {
        $this->lti13Service = $lti13Service;
        $this->cookie = $cookie;

    }

    public function index(Request $request) {
//        Cookie::queue('hello', 'value', 3600);
        $name = 'hello1';
        $value = '12334455';
        $minutes = 60;
        $this->cookie->setCookie($name, $value, $minutes);
    }

    public function getCookie(Request $request) {
        dd(Cookie::get('hello1'));
    }
}
