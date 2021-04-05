<?php

namespace App\Http\Middleware;

use App\Traits\ManagesAnonymousUsers;
use Closure;

/**
 * Class ListsAuthenticationSession
 * Assigns guest users to an "anonymous" user account so they can access this page & presence channels
 */
class AuthenticateAnonymousUsers extends Authenticate
{
	use ManagesAnonymousUsers;

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next, ...$guards) {
		if (!$request->hasSession() || !$request->user()) {
			// Guest User
			$user = $this->getGuestUserFromCookie($request);
			if (!$user) {
				$user = $this->makeAnonymousUser();
			}

			auth()->setUser($user);
			return $next($request)->cookie('guest_user', $user, null, null, null, null, true);
		}

		return parent::handle($request, $next, ...$guards);
	}
}
