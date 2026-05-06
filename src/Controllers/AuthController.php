<?php

declare(strict_types=1);

namespace Swarm\Controllers;

use Swarm\Helpers\Response;
use Swarm\Helpers\Url;
use Swarm\Middleware\Csrf;
use Swarm\Models\Session;
use Swarm\Models\Setting;

/**
 * AuthController — Operator login/logout.
 */
class AuthController
{
    /**
     * GET /operator/login — Show the login form.
     */
    public function show(): void
    {
        // Already logged in?
        if (Session::validate()) {
            Response::redirect(Url::control('/operator'));
        }

        Response::view('login', [
            'csrfField' => Csrf::field(),
            'error'     => Response::flash('error'),
        ], 'public');
    }

    /**
     * POST /operator/login — Authenticate the operator.
     */
    public function store(): void
    {
        Csrf::validate();

        $password = $_POST['password'] ?? '';
        $hash     = Setting::get('operator_password_hash', '');

        if (!$hash || !password_verify($password, $hash)) {
            Response::back(['error' => 'Invalid password.']);
        }

        Session::create();
        Session::cleanup(); // Remove expired sessions

        Response::redirect(Url::control('/operator'));
    }

    /**
     * POST /operator/logout — Destroy the session.
     */
    public function destroy(): void
    {
        Session::destroy();
        Response::redirect(Url::control('/operator/login'));
    }
}
