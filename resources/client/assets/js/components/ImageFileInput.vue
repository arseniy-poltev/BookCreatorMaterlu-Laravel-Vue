<template>
  <div>
    <div v-if="img!=null" class="img-container">
      <div v-if="loading">
        <i class="fa fa-spin fa-refresh"></i> Loading
      </div>
      <div v-if="!loading">
        <img :src="img" class="img-responsive my-image" border="5" />
        <button class="btn btn-danger btn-sm button-div" @click="onClearImage">ClearImage</button>
      </div>

      <!-- <button v-if="xprops.module=='QuestionsEdit'" class="btn btn-primary btn-sm" @click="setDefaultImg">Default</button> -->
    </div>
    <input
      ref="imgInput"
      type="file"
      :accept="accept?accept:'image/*'"
      v-on:change="onImageChange"
      class="form-control"
      :disabled="loading"
    />
  </div>
</template>

<script>
export default {
  props: ["initial", "accept"],
  data() {
    return {
      img: null,
      imageFile: null,
      loading: false
    };
  },
  watch: {
    initial: function(val) {
      this.img = this.initial;
      this.imageFile = this.initial;
    },
    img: function(val) {
      this.$emit("update:base64", val);
      console.log("changed!");
    },
    imageFile: function(val) {
      this.$emit("update:image", val);
      console.log("changed!");
    }
  },
  computed: {},
  created() {
    this.img = this.initial;
    this.imageFile = this.initial;
    console.log("image file input created!");
    //this.resetState();
  },
  destroyed() {},
  methods: {
    onClearImage() {
      this.$refs.imgInput.value = null;
      this.img = null;
      this.imageFile = null;
    },
    resetImage() {
      this.$refs.imgInput.value = null;
    },
    onImageChange(e) {
      let files = e.target.files || e.dataTransfer.files;
      if (!files.length) return;
      this.loading = true;
      let reader = new FileReader();
      reader.onload = e => {
        this.img = e.target.result;
        this.loading = false;
      };
      this.imageFile = files[0];
      reader.readAsDataURL(files[0]);
    }
  }
};
</script>
<style scoped>
.my-image {
  margin: 5px 0px 5px 0px;
  /* display: inline; */
  /* border: #576562; */
  /* width: 60px; */
  /* height: 60px; */
  max-width: 160px;
  border-radius: 6px;
  /* border-style: dashed; */
}
.img-container {
  position: relative;
}
.button-div {
  position: absolute;
  display: inline;
  bottom: 0;
  margin-left: 165px;
}
</style>


