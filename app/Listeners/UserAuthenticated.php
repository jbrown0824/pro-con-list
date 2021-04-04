<?php

namespace App\Listeners;

use App\Events\UserReplacedGuest;
use App\Traits\ManagesAnonymousUsers;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cookie;

class UserAuthenticated
{
	use ManagesAnonymousUsers;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
    	if (!$event->user->guest) {
    		// Logged in as a real user
    		$guestUser = $this->getGuestUserFromCookie(request());
			if ($guestUser) {
				broadcast(new UserReplacedGuest($event->user, $guestUser));
				cookie()->forget('guest_user');
			}
		}
    }
}
