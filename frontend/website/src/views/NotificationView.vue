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
            "user_id": 1,
        })
        .then(notifications => {
            notifications.forEach(notification => {
                notification.text = notification.text.replace(/<a/g, "<RouterLink");
                notification.text = notification.text.replace(/<\/a>/g, "</RouterLink>");
                notification.text = decodeURIComponent(notification.text);

            });
            this.notifications = notifications;
        });
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
                            :class="{'unread': typeof notification.read_at === 'undefined'}">
                            <span class="container block" v-html="notification.text"></span>
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