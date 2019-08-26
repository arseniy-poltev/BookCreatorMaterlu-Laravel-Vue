<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Characters</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <form @submit.prevent="submitForm" novalidate>
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Create</h3>
              </div>

              <div class="box-body">
                <!-- <back-buttton></back-buttton> -->
                <!-- <router-link
                  v-if="$can('character_access')"
                  :to="{ name: 'characters.index', id: $route.params.id}"
                  class="btn btn-default btn-sm"
                >
                  <span class="glyphicon glyphicon-chevron-left"></span> Back to all Characters
                </router-link>-->
                <router-link :to="{ name: 'characters.all'}" class="btn btn-default btn-sm">
                  <span class="glyphicon glyphicon-chevron-left"></span> Back to all Characters
                </router-link>
              </div>

              <bootstrap-alert />

              <div class="box-body">
                <div class="form-group">
                  <label for="name">Name *</label>
                  <input
                    type="text"
                    class="form-control"
                    name="name"
                    placeholder="Enter Name *"
                    :value="item.name"
                    @input="updateName"
                  />
                </div>
                <div class="form-group">
                  <label for="description">Image</label>
                  <image-file-input
                    ref="imgView"
                    v-bind:image.sync="image"
                    :initial="null"
                    :accept="'image/svg+xml'"
                  ></image-file-input>
                </div>
              </div>

              <div class="box-footer">
                <vue-button-spinner
                  class="btn btn-primary btn-sm"
                  :isLoading="loading"
                  :disabled="loading"
                >Save</vue-button-spinner>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>
  </section>
</template>


<script>
import { mapGetters, mapActions } from "vuex";

export default {
  data() {
    return {
      // Code...
      image: null,
      bookId: this.$route.params.id
    };
  },
  computed: {
    ...mapGetters("CharactersSingle", ["item", "loading"])
  },
  watch: {
    "$route.params.id": function() {
      this.init();
    }
  },
  created() {
    // Code ...
    this.init();
  },
  destroyed() {
    this.resetState();
  },
  methods: {
    ...mapActions("CharactersSingle", [
      "storeData",
      "resetState",
      "setName",
      "setImage",
      "setBookId"
    ]),
    init() {
      this.image = null;
      this.resetState();
      this.setBookId(this.$route.params.id);
    },
    updateName(e) {
      this.setName(e.target.value);
    },
    submitForm() {
      this.setImage(this.image);
      this.storeData()
        .then(() => {
          this.$router.push({
            name: "characters.all"
            // params: { id: this.bookId }
          });
          this.$eventHub.$emit("create-success");
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
};
</script>


<style scoped>
</style>
