<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Character</h1>
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
                <!-- <router-link
                  v-if="item.book_id > 0&&$can('character_access')"
                  :to="{ name: 'characters.index',params: { id: item.book_id}}"
                  class="btn btn-default btn-sm"
                >
                  <span class="glyphicon glyphicon-chevron-left"></span> Back to all Characters
                </router-link>-->
                <router-link :to="{ name: 'characters.all'}" class="btn btn-default btn-sm">
                  <span class="glyphicon glyphicon-chevron-left"></span> Back to all Characters
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
                  <label for="description">Character SVG</label>
                  <image-file-input ref="imgView" v-bind:image.sync="image" :initial="item.img_url"></image-file-input>
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
    ...mapGetters("CharactersSingle", ["item", "loading"])
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
    ...mapActions("CharactersSingle", [
      "fetchData",
      "updateData",
      "resetState",
      "setName",
      "setImage"
    ]),
    init() {
      this.resetState();
      this.fetchData(this.$route.params.id).then(() => {
        this.image = this.item.thumb_url;
      });
    },
    updateName(e) {
      this.setName(e.target.value);
    },
    submitForm() {
      this.setImage(this.image);
      this.updateData()
        .then(() => {
          this.$router.push({
            name: "characters.all"
            // params: { id: this.item.book_id }
          });
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
