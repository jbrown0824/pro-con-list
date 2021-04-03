<template>
	<app-layout>
		<template #header>
			<h2 class="font-semibold text-xl text-gray-800 leading-tight">
				List
			</h2>
		</template>

		<div>
			<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
				Users Online: {{ usersOnline.map(user => user.name).join(', ') }}
				<form action @submit.prevent="onSubmit">
					<select v-model="form.type">
						<option value="Pro">Pro</option>
						<option value="Con">Con</option>
					</select>
					<input v-model="form.text" :placeholder="`Enter your ${form.type}`">
					<input type="submit" value="Add">
					<br><br>
					<span v-if="typists.length > 1">{{ typists[0].name }} (+{{typists.length - 1}} other{{ typists.length > 2 ? 's' : ''}} are typing...</span>
					<span v-else-if="typists.length === 1">{{ typists[0].name }} is typing...</span>
				</form>
				{{ $page.props.list }}
			</div>
		</div>
	</app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
	import JetSectionBorder from '@/Jetstream/SectionBorder'

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
				usersTyping: {},
				list: {},
				listId: this.$page.props.listId,
				isTypingTimeout: null,
				users: {},
			};
		},

		computed: {

			typists() {
				return Object.values(this.usersTyping).filter(({ isTyping }) => !!isTyping);
			},

			usersOnline() {
				return Object.values(this.users);
			}

		},

		watch: {
			'form.text': function(newValue) {
				this.setIsTyping(true);

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
				})
				.error((error) => {
					console.error('error?', error);
				})
				.listen('list.updated', (e) => {
					console.log('ListUpdated!', e);
				})
				.listenForWhisper('typing', (e) => {
					if (typeof this.usersTyping[e.name] !== 'undefined') {
						clearTimeout(this.usersTyping[e.name].timeout);
					}

					this.usersTyping[e.name] = {
						isTyping: e.isTyping,
						name: e.name,
						timeout: setTimeout(() => {
							this.usersTyping[e.name].isTyping = false;
						}, 2500)
					};
				});

			window.addEventListener('beforeunload', () => {
				console.log('before unload called!');
				Echo.leave(`list.${this.$page.props.listId}`);
			});
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
				console.log('whispering typing', isTyping ? 'yes' : 'no');
				channel
					.whisper('typing', {
						isTyping,
						name: this.$page.props.user ? this.$page.props.user.name : 'Anonymous Cucumber',
					});
			},

			resetTypingTimeout() {
				clearTimeout(this.isTypingTimeout);
				this.isTypingTimeout = setTimeout(() => this.setIsTyping(false), 2500);
			}

		},

		components: {
			AppLayout,
			JetSectionBorder,
		},
	}
</script>
