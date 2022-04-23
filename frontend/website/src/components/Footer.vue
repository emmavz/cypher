<template>
    <footer class="footer">
        <div class="container">
            <div class="footer__controls">
                <ul class="flex justify-center items-center">
                    <li><RouterLink :to="{name: 'home'}"><img src="@/assets/img/home-icon.svg" alt=""></RouterLink></li>
                    <li><a href="#"><img src="/src/assets/img/search-icon.svg" alt=""></a></li>
                    <li><a href="#" class="active"><img src="/src/assets/img/plus-white-icon.svg" alt=""></a></li>
                    <li><a href="#"><img src="/src/assets/img/notification-icon.svg" alt=""></a></li>
                    <li><a href="#"><img src="/src/assets/img/profile-icon.svg" alt=""></a></li>
                </ul>
            </div>
        </div>
    </footer>
</template>