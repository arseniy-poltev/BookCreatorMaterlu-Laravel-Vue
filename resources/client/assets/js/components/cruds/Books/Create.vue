<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Books</h1>
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
                <router-link
                  v-if="$can('book_access')"
                  :to="{ name: 'books.index'}"
                  class="btn btn-default btn-sm"
                >
                  <span class="glyphicon glyphicon-chevron-left"></span> Back to all Books
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
                  <label for="description">Logo Image</label>
                  <image-file-input ref="imgView" v-bind:image.sync="image" :initial="null"></image-file-input>
                </div>
                <div class="form-group">
                  <label for="users">Artists *</label>
                  <v-select
                    name="users"
                    label="name"
                    @input="updateArtist"
                    :value="item.user_id"
                    :options="usersAll"
                    :disabled="usersAll.length == 0"
                  />
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
      image: null
    };
  },
  computed: {
    ...mapGetters("BooksSingle", ["item", "loading", "usersAll"])
  },
  created() {
    // Code ...
    this.init();
  },
  destroyed() {
    this.resetState();
  },
  methods: {
    ...mapActions("BooksSingle", [
      "storeData",
      "fetchUsersAll",
      "resetState",
      "setName",
      "setLogo",
      "setArtist"
    ]),
    init() {
      this.image = null;
      this.resetState();
      this.fetchUsersAll();
    },
    updateName(e) {
      this.setName(e.target.value);
    },
    updateArtist(value) {
      this.setArtist(value);
    },
    submitForm() {
      this.setLogo(this.image);
      this.storeData()
        .then(() => {
          this.$router.push({ name: "books.index" });
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
