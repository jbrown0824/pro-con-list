<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ListingUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $listId;
	public $list;

	/**
	 * Create a new channel instance.
	 *
	 * @param $listId
	 * @param $list
	 */
	public function __construct($listId, $list)
	{
		$this->listId = $listId;
		$this->list = $list;
	}

	public function broadcastOn()
	{
		return new PresenceChannel('list.' . $this->listId);
	}

	/**
	 * The event's broadcast name.
	 *
	 * @return string
	 */
	public function broadcastAs()
	{
		return 'list.updated';
	}
}
