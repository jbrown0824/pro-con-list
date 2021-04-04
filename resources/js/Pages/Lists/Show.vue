<template>
	<app-layout>
		<template #header>
			<h2 class="font-semibold text-xl text-gray-800 leading-tight">
				List
			</h2>
		</template>

		<div>
			<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8" id="container">
				Users Online: <span v-for="(user, index) in usersOnline" :key="`online-user-${user.id}`"><span :style="`color: ${user.color};`">{{ user.name }}</span>{{ user.id === $page.props.user.id ? ' (This is you)' : ''}}{{ index < (usersOnline.length - 1) ? ',' : ''}}&nbsp;</span>
				<form action @submit.prevent="onSubmit">
					<select v-model="form.type">
						<option value="Pro">Pro</option>
						<option value="Con">Con</option>
					</select>
					<input v-model="form.text" :placeholder="`Enter your ${form.type}`">
					<input type="submit" value="Add">
					<br><br>
					<span v-if="typists.length > 1">{{ typists[0].user.name }} (+{{typists.length - 1}} other{{ typists.length > 2 ? 's' : ''}} are typing...</span>
					<span v-else-if="typists.length === 1">{{ typists[0].user.name }} is typing...</span>
				</form>
				<table>
					<thead>
					<tr>
						<td>Pro</td>
						<td>Con</td>
					</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<div v-for="item in (list['Pro'] || [])" :key="`list-${item.id}`">{{ item.text }} - {{ item.author }}</div>
							</td>
							<td>
								<div v-for="item in (list['Con'] || [])" :key="`list-${item.id}`">{{ item.text }} - {{ item.author }}</div>
							</td>
						</tr>
					</tbody>
				</table>

				<div id="pointers">
					<div v-for="state in pointers" :key="`user-pointer-${ state.id }`" class="pointer" :style="`left: ${state.mousePosition.x}px; top:${state.mousePosition.y}px; color:${state.user.color};`">{{ state.user.name }}</div>
				</div>
			</div>
		</div>
	</app-layout>
</template>

<style>
	#pointers {
		position: relative;
	}

	#pointers .pointer {
		position: fixed;
		width: 15px;
		height: 22px;
		background: url("https://uploads.codesandbox.io/uploads/user/88acfe5a-77fc-498c-98ee-d1b0b303f6a8/tC4n-pointer.png")
		no-repeat -4px 0;
	}

	.focused {
		border: 2px solid red;
	}
</style>

<script>
    import AppLayout from '@/Layouts/AppLayout'
	import JetSectionBorder from '@/Jetstream/SectionBorder'
	import generateQuerySelector from '../../utils/generateQuerySelector';
	import { trackFocus } from '../../utils/track-focus';

	let channel;

    function anonymousUser() {
    	const nouns = ['Platypus', 'Cucumber', 'Orangutan', 'Flower', 'River', 'Daisy'];

    	return nouns[Math.floor(Math.random() * nouns.length)];
	}

	export default {
		props: ['sessions', 'listId'],

		data() {
			return {
				fakeUsername: anonymousUser(),
				form: {
					type: 'Pro',
					text: '',
				},
				isTyping: false,
				// Record<string|number, UserState>
				userState: {},
				list: this.$page.props.list || {},
				listId: this.$page.props.listId,
				isTypingTimeout: null,
				users: {},
			};
		},

		computed: {

			typists() {
				return Object.values(this.userState).filter(({ user, isTyping }) => !!this.users[user.id] && !!isTyping);
			},

			usersOnline() {
				return Object.values(this.users);
			},

			pointers() {
				return Object.values(this.userState).filter(state => !!this.users[state.user.id] && state.mousePosition && state.mousePosition.x);
			},

			focusedElements() {
				return Object.values(this.userState).filter(state => !!this.users[state.user.id] && state.focus);
			},

		},

		watch: {
			'form.text': function(newValue) {
				this.setIsTyping(newValue && newValue.length);
			},

			focusedElements: function(focusedElements, prevFocusedElements) {
				// const unfocusedElements = prevFocusedElements.filter(({focus} = {}) => focusedElements.includes(focus));
				document.querySelectorAll('.focused')
					.forEach(element => {
						element.classList.remove('focused')
						element.style.borderColor = 'auto';
					});

				focusedElements.forEach(state => {
					const e = document.querySelector(state.focus);
					if (e) {
						e.classList.add('focused');
						e.style.borderColor = state.user.color;
					}
				});
			},
		},

		mounted() {
			channel = Echo.join(`list.${this.$page.props.listId}`);
			channel
				.here((users) => {
					console.log('users here?', users);
					users.forEach(({ user }) => {
						this.users[user.id] = user;
					});
				})
				.joining(({ user }) => {
					console.log('joining', user);
					this.users[user.id] = user;
				})
				.leaving(({ user }) => {
					console.log('leaving', user);
					delete this.users[user.id];
					delete this.userState[user.id];
				})
				.error((error) => {
					console.error('error?', error);
				})
				.listen('.list.updated', (e) => {
					console.log('ListUpdated!', e);
					this.list = e.list;
				})

				/**
				 * TODO: Rather than run timeouts for all of these (which is cpu intensive and may not fire, leading to bad state),
				 * just store a date on each trackable event and filter out stale events via the computed properties.
				 * Have a single loop periodically forcefully update computed props.
				 *
				 * TODO: All trackable events should be interfaced to simplify all of this code
				 *
				 * TODO: Move all tracking code to a class, perhaps implement vue 3's reactivity code
				 */

					.listenForWhisper('typing', (e) => {

					this.userState[e.user.id] = this.userState[e.user.id] || {
						user: e.user,
						isTyping: false,
						timeouts: {},
					};

					clearTimeout(this.userState[e.user.id].timeouts.typing);

					this.userState[e.user.id].timeouts = this.userState[e.user.id].timeouts || {};
					this.userState[e.user.id].isTyping = e.isTyping;
					this.userState[e.user.id].timeouts.typing = setTimeout(() => {
						this.userState[e.user.id].isTyping = false
					}, 2500);
				})
				.listenForWhisper('mousemove', (e) => {
					this.userState[e.user.id] = this.userState[e.user.id] || {
						user: e.user,
						isTyping: false,
						timeouts: {},
					};

					clearTimeout(this.userState[e.user.id].timeouts.mousemove);

					const position = { ...e.position };
					const bounding = document.querySelector('#container').getBoundingClientRect();
					position.x -= (position.containerXOffset - bounding.left);

					this.userState[e.user.id].timeouts = this.userState[e.user.id].timeouts || {};
					this.userState[e.user.id].mousePosition = position;
					this.userState[e.user.id].timeouts.mousemove = setTimeout(() => {
						this.userState[e.user.id] = {
							...(this.userState[e.user.id] || {}),
							mousePosition: null,

						};
					}, 25000);
				})
				.listenForWhisper('active-element', (e) => {
					console.log('got active element change');
					this.userState[e.user.id] = this.userState[e.user.id] || {
						user: e.user,
						isTyping: false,
						timeouts: {},
					};

					clearTimeout(this.userState[e.user.id].timeouts.activeElement);

					this.userState[e.user.id].timeouts = this.userState[e.user.id].timeouts || {};
					this.userState[e.user.id].focus = e.element;
					this.userState[e.user.id].timeouts.activeElement = setTimeout(() => {
						delete this.userState[e.user.id].focus;
					}, 25000);
				});

			window.addEventListener('beforeunload', () => {
				console.log('before unload called!');
				Echo.leave(`list.${this.$page.props.listId}`);
			});

			// Debounce
			const mousemove = _.throttle((e) => this.handleMouseMove(e), 100);
			window.addEventListener('mousemove', mousemove);

			// window.addEventListener('mousemove', (e) => this.handleMouseMove(e));

			trackFocus(
				(e) => this.handleFocusChange(e, 'FOCUS'),
				(e) => this.handleFocusChange(e, 'BLUR'),
				document
			);
		},

		beforeUnmount() {
			console.log('beforeUnmount!', this.listId);
			Echo.leave(`list.${this.listId}`);
		},

		methods: {

			async onSubmit() {
				console.log('submitted', this.form);
				const { data } = await axios.post(`/lists/${ this.$page.props.listId}`, this.form);
				console.log('data', data);
				this.form.text = '';
				this.setIsTyping(false);
				clearTimeout(this.isTypingTimeout);
			},

			setIsTyping(isTyping) {
				if (isTyping || this.isTyping) {
					this.resetTypingTimeout();
				}
				this.isTyping = isTyping;
				this._whisper('typing', {
					isTyping,
				});
			},

			resetTypingTimeout() {
				clearTimeout(this.isTypingTimeout);
				this.isTypingTimeout = setTimeout(() => this.setIsTyping(false), 2500);
			},

			handleMouseMove(e) {
				const bounding = document.querySelector('#container').getBoundingClientRect();
				this._whisper('mousemove', {
					position: {
						x: e.pageX,
						y: e.pageY,
						containerXOffset: bounding.left,
					},
				});
			},

			handleFocusChange(e, change) {
				this._whisper('active-element', {
					element: change === 'FOCUS' ? generateQuerySelector(e.target) : null,
				});
			},

			_whisper(name, data) {
				channel.whisper(name, {
					user: this.$page.props.user,
					...data,
				})
			},
		},

		components: {
			AppLayout,
			JetSectionBorder,
		},
	}
</script>
