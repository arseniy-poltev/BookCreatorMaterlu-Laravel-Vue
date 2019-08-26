<template>
  <div>
    <router-link
      v-if="$can(xprops.permission_prefix + 'view')"
      :to="{ name: 'characters.index', params: { id: row.id } }"
      class="btn btn-danger"
    >{{row.characters_count}} Characters</router-link>
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
