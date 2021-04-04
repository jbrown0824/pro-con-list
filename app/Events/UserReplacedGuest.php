<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserReplacedGuest implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	/**
	 * @var User
	 */
	public $user;
	/**
	 * @var User
	 */
	public $guestUser;

	/**
	 * Create a new event instance.
	 *
	 * @param User $user
	 * @param User $guestUser
	 */
    public function __construct(User $user, User $guestUser)
    {
        // TODO - Notify Presence Channels to convert an anonymous user to an authenticated user
		$this->user = $user;
		$this->guestUser = $guestUser;
	}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
