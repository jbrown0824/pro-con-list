interface UserState {
	user: User
	lastTyped: null | Date;
	position: null | UserMousePosition;
	focus: UserFocus;
}

interface User {
	id: number;
	name: string;
	color: string;
	profile_photo_url: string;
	guest: boolean;
}

interface UserMousePosition {
	x: number;
	y: number;
	recordedAt: Date;
}

interface UserFocus {
	element: string | HTMLElement;
	recordedAt: Date;
}
