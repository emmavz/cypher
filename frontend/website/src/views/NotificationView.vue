<script>
export default ({
    data() {
        return {
        //   notifications: [
        //       {
        //           name: '<b>Allison Vega</b> just invested <b>20 CPHR</b>.',
        //           url: '0',
        //           unread: true
        //       },
        //       {
        //           name: '<b>Debby Anonymous</b> just invested <b>14 CPHR</b>.',
        //           url: '1',
        //           unread: false
        //       },
        //       {
        //           name: 'Congratulations on your first article: <b>Tokenomics</b>!',
        //           url: '2',
        //           unread: false
        //       },
        //       {
        //           name: 'Your article <b> School’s Out</b> has been liquidated. <b>See statistics here</b>',
        //           url: '3',
        //           unread: true
        //       },
        //       {
        //           name: 'Welcome to Cypher! <b>Get started here</b>.',
        //           url: '4',
        //           unread: false
        //       }
        //   ],
            notifications: []
        }
    },
    created() {
        this.sendApiRequest('get_notifications', {
            "user_id": window.user_id,
        })
        .then(userNotifications => {
            userNotifications.forEach(userNotification => {
                userNotification.notification.text = userNotification.notification.text.replace(/<a/g, "<RouterLink");
                userNotification.notification.text = userNotification.notification.text.replace(/<\/a>/g, "</RouterLink>");
                userNotification.notification.text = decodeURIComponent(userNotification.notification.text);

            });
            this.notifications = userNotifications;
        });
    },
    methods: {
        notificationText(text) {
            return {
                template: text,
            }
        },
        readNotification(index, notificationId) {
            this.sendApiRequest('read_notification', {
                "notification_id": notificationId,
                "user_id": window.user_id
            }).then((time) => {
                this.notifications[index].read_at = time[0];
            });
        }
    },
})
</script>

<template>

    <div class="app-wp">

        <Header />

        <!-- Content -->
        <div class="content i-wrap pt-8">

            <div>
                <div class="container">
                    <h2 class="mb-7 ml-2">Notifications</h2>
                </div>

                <ul class="notifications" v-if="!isError">
                    <template v-if="notifications.length">
                        <li v-for="(notification, index) in notifications" :key="index"
                            :class="{ 'unread cursor-pointer': notification.read_at == null}"
                            @click="notification.read_at == null ? readNotification(index, notification.id) : ''">
                            <span class="container block">
                                <component v-bind:is="notificationText(notification.notification.text)" />
                            </span>
                        </li>
                    </template>
                    <template v-else>
                        <div class="container block text-center">No new notification!</div>
                    </template>
                </ul>
            </div>

            <Error />
        </div>

    </div>

</template>