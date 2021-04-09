import { reactive } from 'vue';


class ListChannel {
	userState;
	user;
	channel;

	constructor(userState, user, channel) {
		this.userState = userState;
		this.user = user;
		this.channel = channel;

		this.startWhisperListeners();
	}

	startWhisperListeners() {
		this.channel
			.listenForWhisper('typing', (e) => {
				ensureUserState(e.user);

				this.userState[e.user.id].isTyping = e.isTyping;
				trackUserStateChange(e.user, 'typing', () => {
					if (this.userState[e.user.id]) {
						this.userState[e.user.id].isTyping = false;
					}
				}, 2500);
			})
			.listenForWhisper('mousemove', (e) => {
				ensureUserState(e.user);

				const position = { ...e.position };
				const bounding = document.querySelector('#container').getBoundingClientRect();
				position.x -= (position.containerXOffset - bounding.left);

				this.userState[e.user.id].mousePosition = position;
				trackUserStateChange(e.user, 'mousemove', () => {
					if (this.userState[e.user.id]) {
						this.userState[e.user.id].mousePosition = null;
					}
				}, 25000);
			})
			.listenForWhisper('active-element', (e) => {
				ensureUserState(e.user);

				this.userState[e.user.id].focus = e.element;
				trackUserStateChange(e.user, 'activeElement',() => {
					if (this.userState[e.user.id]) {
						this.userState[ e.user.id ].focus = null;
					}
				}, 25000);
			});
	}

	ensureUserState = (user) => {
		if (!this.userState[user.id]) {
			this.userState[user.id] = reactive({
				user,
				lastTyped: null,
				mousePosition: null,
				timeouts: {},
			});
		}
	}
}
