export interface Position {
	x: number;
	y: number;
}

export interface UserState {
	name: string;
	isTyping: boolean;
	timeouts: Record<string, number>;
	mousePosition?: Position;
	focus?: string | null;
}
