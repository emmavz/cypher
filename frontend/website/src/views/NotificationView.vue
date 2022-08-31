<script>
export default {
  data() {
    return {
      notifications: [],
      notificationsOffset: 0,
      notificationsLimit: 30,
      stopscrollAjax: false,
    };
  },
  created() {
    this.getNotifications();
  },
  mounted() {
    const contentElm = document.querySelector('.content');
    contentElm.onscroll = () => {
      if (!this.stopscrollAjax) {
        let bottomOfWindow = contentElm.scrollTop + contentElm.clientHeight >= contentElm.scrollHeight - window.bottomGap;
        if (bottomOfWindow) {
          this.notificationsOffset += this.notificationsLimit;
          this.getNotifications();
        }
      }
    }
  },
  methods: {
    getNotifications() {
      this.stopscrollAjax = true;
      this.sendApiRequest("get_notifications", {
        "offset": this.notificationsOffset,
        "limit": this.notificationsLimit
      }).then((userNotifications) => {

          if (userNotifications.length) {

            userNotifications.forEach((userNotification) => {
              userNotification.notification.text =
                userNotification.notification.text.replace(/<a/g, "<RouterLink");
              userNotification.notification.text =
                userNotification.notification.text.replace(/<\/a>/g, "</RouterLink>");
              userNotification.notification.text = decodeURIComponent(
                userNotification.notification.text
              );
            });
            this.notifications = this.notifications.concat(userNotifications);
            this.stopscrollAjax = false;
          }
          else {
            this.stopscrollAjax = true;
          }

      }).catch(() => {
        this.stopscrollAjax = false;
      });
    },
    notificationText(text) {
      return {
        template: text,
      };
    },
    readNotification(index, notificationId) {
      this.sendApiRequest("read_notification", {
        notification_id: notificationId,
      }, true).then((time) => {
        this.notifications[index].read_at = time[0];
      });
    },
  },
};
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
            <li
              v-for="(notification, index) in notifications"
              :key="index"
              :class="{ 'unread cursor-pointer': notification.read_at == null }"
              @click="
                notification.read_at == null
                  ? readNotification(index, notification.id)
                  : ''
              "
            >
              <span class="container block">
                <component
                  v-bind:is="notificationText(notification.notification.text)"
                />
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
