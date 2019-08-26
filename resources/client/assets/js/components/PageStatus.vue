<template>
  <span class="name-part" v-bind:class="status">{{file_name}}</span>
</template>
<script>
export default {
  props: ["file_name", "status"],
  destroyed() {}
};
</script>
<style scoped>
.name-part {
  /* float: right; */
  position: absolute;
  display: flex;
  font-weight: 800;
  border-radius: 2px;
  color: black;
  margin-left: 2px;
}
.not_check {
  background-color: bisque;
}
.correct {
  background-color: forestgreen;
  color: white;
}
.incorrect {
  background-color: brown;
  color: white;
}
</style>



