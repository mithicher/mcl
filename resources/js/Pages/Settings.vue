<template>
	<layout title="Settings">
		<div class="bg-white px-4 pt-6 pb-4 relative shadow-sm z-20">
			<div class="max-w-6xl mx-auto">
				<link-to to="/dashboard" class="mb-4">
					<icon name="chevron-left" class="-ml-2"></icon>Back to Dashboard
				</link-to>

				<heading size="heading">Settings</heading>
			</div>
		</div>

		<div class="px-4 lg:px-6 py-5 md:py-10 bg-gray-100">
			<div class="max-w-6xl mx-auto">
				<flash-message class="mb-8" />

				<div class="md:flex md:flex-wrap mb-10 -mx-4">
					<div class="md:w-1/3 px-4">
						<heading size="large" class="mb-1">Personal Details</heading>
						<heading class="mb-4">Edit your name, date of birth and so on.</heading>
					</div>
					<div class="md:w-2/3 px-4">
						<card with-footer>
							<text-input
								class="mb-4"
								label="Name"
								v-model="user.name"
								:errors="errors.name"
								@keydown="delete errors.name"
							></text-input>
							<div class="mb-2">
								<text-input label="Email" disabled v-model="user.email" :bordered="false"></text-input>
								<p class="text-sm mb-0 mt-2">
									Please
									<mail-to subject="I want to change my email address">contact admin</mail-to>&nbsp;to change/update
									your email address
								</p>
							</div>

							<template #footer>
								<loading-button ref="updateProfileButton" @click="updateProfile">Update Profile</loading-button>
							</template>
						</card>
					</div>
				</div>

				<div class="md:flex md:flex-wrap mb-10 -mx-4">
					<div class="md:w-1/3 px-4">
						<heading size="large" class="mb-1">Change Password</heading>
						<heading class="mb-4">
							Update your password here. We recommend that you set a strong
							password that is atleast 8 characters long and includes a mix of
							letters, numbers and symbols.
						</heading>
					</div>
					<div class="md:w-2/3 px-4">
						<card with-footer>
							<text-input
								type="password"
								class="mb-4"
								label="Current Password"
								v-model="resetform.current_password"
								:errors="errors.current_password"
							></text-input>
							<text-input
								type="password"
								class="mb-4"
								label="New Password"
								v-model="resetform.password"
								:errors="errors.password"
							></text-input>
							<text-input
								type="password"
								label="Confirm New Password"
								v-model="resetform.password_confirmation"
								:errors="errors.password_confirmation"
							></text-input>

							<template #footer>
								<loading-button
									type="button"
									ref="changePasswordButton"
									@click="changePassword"
								>Update Password</loading-button>
							</template>
						</card>
					</div>
				</div>

				<!-- <div class="md:flex md:flex-wrap mb-10 -mx-4">
					<div class="md:w-1/3 px-4">
						<heading size="large" class="mb-1">Notifications</heading>
						<heading class="mb-4">
							Control the newsletter subscription and email
							notifications
						</heading>
					</div>
					<div class="md:w-2/3 px-4">
						<card :is-padding="false" with-footer>
							<list-group>
								<div slot="listbody" class="pr-10">
									<heading size="large" class="mb-1">Email Notifications</heading>
									<heading>
										Lorem ipsum dolor sit, amet consectetur adipisicing elit.
										Nulla dolorem odit voluptas, saepe a earum.
									</heading>
								</div>

								<template slot="listaction">
									<switch-input color="#1abc9c" v-model="notification"></switch-input>
								</template>
							</list-group>
							<list-group class="border-t">
								<div slot="listbody" class="pr-10">
									<heading size="large" class="mb-1">Text/SMS Notifications</heading>
									<heading>
										Lorem ipsum dolor sit, amet consectetur adipisicing elit.
										Nulla dolorem odit voluptas, saepe a earum.
									</heading>
								</div>

								<template slot="listaction">
									<switch-input color="#1abc9c" v-model="notification"></switch-input>
								</template>
							</list-group>
							<list-group class="border-t">
								<div slot="listbody" class="pr-10">
									<heading size="large" class="mb-1">Slack Notifications</heading>
									<heading>
										Lorem ipsum dolor sit, amet consectetur adipisicing elit.
										Nulla dolorem odit voluptas, saepe a earum.
									</heading>
								</div>

								<template slot="listaction">
									<switch-input color="#1abc9c" v-model="notification"></switch-input>
								</template>
							</list-group>

							<template #footer>
								<loading-button>Save Settings</loading-button>
							</template>
						</card>
					</div>
				</div>-->
			</div>
		</div>
	</layout>
</template>

<script>
import Layout from "@/Shared/Layout.vue";
import Heading from "@/Shared/tuis/Heading.vue";
import Card from "@/Shared/tuis/Card.vue";
import ListGroup from "@/Shared/tuis/ListGroup.vue";
import SwitchInput from "@/Shared/tuis/SwitchInput.vue";
import TextInput from "@/Shared/tuis/TextInput.vue";
import LoadingButton from "@/Shared/tuis/LoadingButton.vue";
import LinkTo from "@/Shared/tuis/LinkTo.vue";
import MailTo from "@/Shared/tuis/MailTo.vue";
import Icon from "@/Shared/tuis/Icon.vue";
import Alert from "@/Shared/tuis/Alert.vue";
import FlashMessage from "@/Shared/tuis/FlashMessage.vue";

export default {
	components: {
		Layout,
		Heading,
		Card,
		ListGroup,
		SwitchInput,
		TextInput,
		LoadingButton,
		LinkTo,
		MailTo,
		Icon,
		Alert,
		FlashMessage
	},

	props: ["errors"],

	data() {
		return {
			notification: true,

			user: {
				name: this.$page.auth.user.name,
				email: this.$page.auth.user.email
			},

			resetform: {
				current_password: "",
				password: "",
				password_confirmation: ""
			}
		};
	},

	props: ["errors"],

	methods: {
		changePassword() {
			this.$refs.changePasswordButton.startLoading();
			this.$inertia
				.post(this.route("settings.changePassword"), this.resetform)
				.then(res => {
					if (Object.keys(this.$page.errors).length === 0) {
						this.resetform = {};
					}
					this.$refs.changePasswordButton.stopLoading();
				})
				.catch(() => {});
		},
		updateProfile() {
			this.$refs.updateProfileButton.startLoading();

			this.$inertia
				.post(this.route("settings.updateProfile"), this.user)
				.then(res => {
					this.$refs.updateProfileButton.stopLoading();
				})
				.catch(() => {});
		}
	}
};
</script>
