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
                <h3 class="box-title">Edit</h3>
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

              <div class="row" v-if="loading">
                <div class="col-xs-4 col-xs-offset-4">
                  <div class="alert text-center">
                    <i class="fa fa-spin fa-refresh"></i> Loading
                  </div>
                </div>
              </div>

              <bootstrap-alert />

              <div class="box-body" v-if="!loading">
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
                  <image-file-input ref="imgView" v-bind:image.sync="image" :initial="item.img_url"></image-file-input>
                </div>
                <div class="form-group" v-if="$can('book_create')">
                  <label for="users">Artists *</label>
                  <v-select
                    name="users"
                    label="name"
                    @input="updateArtist"
                    :value="item.user"
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
    this.init();
  },
  destroyed() {
    this.resetState();
  },
  watch: {
    "$route.params.id": function() {
      this.init();
    }
  },
  methods: {
    ...mapActions("BooksSingle", [
      "fetchData",
      "fetchUsersAll",
      "updateData",
      "resetState",
      "setName",
      "setLogo",
      "setArtist"
    ]),
    init() {
      this.resetState();
      if (this.$can("book_create")) {
        this.fetchUsersAll();
      }
      this.fetchData(this.$route.params.id).then(() => {
        this.image = this.item.img_url;
      });
    },
    updateName(e) {
      this.setName(e.target.value);
    },
    updateArtist(value) {
      this.setArtist(value);
    },
    submitForm() {
      this.setLogo(this.image);
      this.updateData()
        .then(() => {
          this.$router.push({ name: "books.index" });
          this.$eventHub.$emit("update-success");
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
