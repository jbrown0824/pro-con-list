<?php

namespace App\Http\Middleware;

use App\Traits\ManagesAnonymousUsers;
use Closure;
use Laravel\Jetstream\Http\Middleware\AuthenticateSession as BaseAuthenticateSession;

/**
 * Class ListsAuthenticationSession
 * Assigns guest users to an "anonymous" user account so they can access this page & presence channels
 */
class ListsAuthenticationSession extends BaseAuthenticateSession
{
	use ManagesAnonymousUsers;

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, $autoAuth = false) {
		if (!$request->hasSession() || !$request->user()) {
			// Guest User
			$user = $this->getGuestUserFromCookie($request);
			if (!$user && $autoAuth) {
				$user = $this->makeAnonymousUser();
			}

			if ($user) {
				auth()->setUser($user);
				return $next($request)->cookie('guest_user', $user, null, null, null, null, true);
			}
		}

		return parent::handle($request, $next);
	}
}
