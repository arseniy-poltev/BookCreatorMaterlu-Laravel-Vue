<template>
  <div>
    <router-link
      :to="{ name: 'characters.layer', params: { id: row.id } }"
      class="btn btn-info"
    >Layer Options</router-link>
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
