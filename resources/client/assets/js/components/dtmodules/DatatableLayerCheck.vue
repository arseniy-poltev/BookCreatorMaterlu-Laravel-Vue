<template>
  <div>
    <router-link
      :to="{ name: 'characters.layer_check', params: { id: row.id } }"
      class="btn btn-danger"
    >Check Layer</router-link>
  </div>
</template>

<script>
export default {
  props: ["row", "xprops"],
  data() {
    return {
      // Code...
    };
  },
  created() {
    // Code...
  }
};
</script>
