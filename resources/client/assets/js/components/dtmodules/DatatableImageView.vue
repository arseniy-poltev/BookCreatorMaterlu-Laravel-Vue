<template>
  <vue-load-image>
    <img slot="image" :src="row.img_url" v-if="row.img_url" class="svg-image" />
    <img slot="preloader" class="preloader" src="/gif/loading.gif" style="width:50px" />
    <div slot="error">Error</div>
  </vue-load-image>
  <!-- <div>
    <img class="my-image" :src="row.logo_url" v-if="row.logo_url" />
    <img class="svg-image" :src="row.img_url" v-if="row.img_url" />
    
  </div>-->
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
<style>
.my-image {
  /* width: 60px; */
  max-width: 164px;
  /* height: 60px; */
  border-radius: 6px;
}
.svg-image {
  /* width: 60px; */
  max-width: 100px;
  /* height: 60px; */
  border-radius: 6px;
}
.preloader {
  /* margin: auto; */

  display: block;
}
</style>
