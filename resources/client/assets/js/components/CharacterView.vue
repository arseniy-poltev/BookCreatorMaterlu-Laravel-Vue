<template>
  <div class="hovereffect" v-if="!deleted">
    <vue-load-image>
      <img slot="image" class="img-responsive" :src="data.thumb_url" v-if="data.img_url" alt />
      <img slot="preloader" class="preloader" src="/gif/loading.gif" style="width:50px" />
      <div slot="error">Error</div>
    </vue-load-image>
    <div class="overlay">
      <h2>{{data.name}}</h2>
      <router-link
        v-if="$can('character_edit')"
        :to="{ name: 'characters.edit' , params: { id: data.id }}"
        class="info edit"
      >
        <i class="fa fa-edit"></i>
      </router-link>
      <router-link
        v-if="$can('character_edit')"
        :to="{ name: 'characters.layer' , params: { id: data.id }}"
        class="info check"
      >
        <i class="fa fa-check"></i>
      </router-link>
      <a class="info delete" v-on:click="destroyData(data.id)">
        <i class="fa fa-trash"></i>
      </a>
    </div>
  </div>
</template>
<script>
export default {
  props: ["data"],
  data() {
    return {
      // Code...
      deleted: false
    };
  },
  methods: {
    destroyData(id) {
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete",
        confirmButtonColor: "#dd4b39",
        focusCancel: true,
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          this.$store
            .dispatch("CharactersIndex/destroyData", id)
            .then(result => {
              this.deleted = true;
              this.$eventHub.$emit("delete-success");
            });
        }
      });
    }
  }
};
</script>
<style scoped>
.preloader {
  margin: auto;

  display: block;
}
.img-responsive {
  /* width: 250px; */
  width: 100%;
}
.hovereffect {
  width: 100%;
  height: 100%;
  float: left;
  overflow: hidden;
  position: relative;
  text-align: center;
  cursor: default;

  background-color: #fff;
  box-shadow: 0 2px 63px 3px rgba(53, 53, 53, 0.1);
  border-radius: 20px;
  /* margin: 0 auto; */
  padding: 8px;
}

.hovereffect .overlay {
  width: 100%;
  height: 100%;
  position: absolute;
  overflow: hidden;
  top: 0;
  left: 0;
}

.hovereffect img {
  display: block;
  position: relative;
  /* -webkit-transition: all 0.4s ease-in; */
  /* transition: all 0.4s ease-in; */
}

.hovereffect:hover img {
  /* filter: url('data:image/svg+xml;charset=utf-8,<svg xmlns="http://www.w3.org/2000/svg"><filter id="filter"><feColorMatrix type="matrix" color-interpolation-filters="sRGB" values="0.2126 0.7152 0.0722 0 0 0.2126 0.7152 0.0722 0 0 0.2126 0.7152 0.0722 0 0 0 0 0 1 0" /><feGaussianBlur stdDeviation="3" /></filter></svg>#filter'); */
  filter: grayscale(1) blur(3px);
  -webkit-filter: grayscale(1) blur(3px);
  /* filter: blur(2px); */
  /* -webkit-filter: blur(2px); */
  /* -webkit-transform: scale(1.2); */
  /* -ms-transform: scale(1.2); */
  /* transform: scale(1.2); */
}

.hovereffect h2 {
  text-transform: uppercase;
  text-align: center;
  position: relative;
  font-size: 17px;
  padding: 10px;
  background: rgba(0, 0, 0, 0.6);
}

.hovereffect a.info {
  display: inline-block;
  text-decoration: none;
  padding: 7px 14px;
  border: 1px solid #fff;
  margin: 50px 0 0 0;
  background-color: transparent;
  cursor: pointer;
}

.hovereffect a.edit {
  background-color: gray;
}
.hovereffect a.check {
  background-color: mediumslateblue;
}
.hovereffect a.delete {
  background-color: lightcoral;
}

.hovereffect a.info:hover {
  box-shadow: 0 0 5px #fff;
}

.hovereffect a.info,
.hovereffect h2 {
  -webkit-transform: scale(0.7);
  -ms-transform: scale(0.7);
  transform: scale(0.7);
  -webkit-transition: all 0.4s ease-in;
  transition: all 0.4s ease-in;
  opacity: 0;
  filter: alpha(opacity=0);
  color: #fff;
  text-transform: uppercase;
}

.hovereffect:hover a.info {
  opacity: 0.6 !important;
}

.hovereffect:hover a.info,
.hovereffect:hover h2 {
  opacity: 1;
  filter: alpha(opacity=100);
  -webkit-transform: scale(1);
  -ms-transform: scale(1);
  transform: scale(1);
}
</style>


