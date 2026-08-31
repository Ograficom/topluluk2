<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;

class FailedPasswordResetLinkRequestResponse implements FailedPasswordResetLinkRequestResponseContract
{
    public function __construct(protected string $status)
    {
    }

    public function toResponse($request)
    {
        if ($this->status === Password::INVALID_USER) {
            return $request->wantsJson()
                ? new JsonResponse(['message' => trans(Password::RESET_LINK_SENT)], 200)
                : back()->with('status', trans(Password::RESET_LINK_SENT));
        }

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($this->status)],
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($this->status)]);
    }
}
