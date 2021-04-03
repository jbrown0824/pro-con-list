<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Cache;

class ListingChannel implements ShouldBroadcast
{

	public $listId;

	/**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
	}

    /**
     * Authenticate the user's access to the channel.
     *
     * @return array|bool
     */
    public function join(User $user, $listId)
    {
    	$this->listId = $listId;
        return [
        	'listId' => $this->listId,
			'user' => auth()->check() ? auth()->user() : [ 'name' => 'Anonymous Cow'],
			'list' => Cache::get('list.' . $listId) ?? [],
		];
    }

	public function broadcastOn() {
		return new PresenceChannel('list.' . $this->listId);
	}
}
