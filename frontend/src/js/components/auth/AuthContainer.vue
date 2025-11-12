<template>
  <div>
    <Login v-if="isLogin" @switch-to-register="isLogin = false" />
    <Register v-else @switch-to-login="isLogin = true" />
  </div>
</template>

<script>
import { ref } from 'vue';
import Login from './Login.vue';
import Register from './Register.vue';

export default {
  name: 'AuthContainer',
  components: {
    Login,
    Register,
  },
  setup() {
    const isLogin = ref(true);

    return {
      isLogin,
    };
  },
};
</script>

