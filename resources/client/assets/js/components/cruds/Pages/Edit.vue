<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Page</h1>
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
                  v-if="item.book_id > 0&&$can('page_access')"
                  :to="{ name: 'pages.index',params: { id: item.book_id}}"
                  class="btn btn-default btn-sm"
                >
                  <span class="glyphicon glyphicon-chevron-left"></span> Back to all Pages
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
                  <page-status :file_name="item.file_name" :status="item.status"></page-status>
                  <br />
                  <!-- <label for="description">Page SVG</label> -->
                  <image-file-input
                    ref="imgView"
                    v-bind:image.sync="image"
                    :initial="item.thumb_url"
                  ></image-file-input>
                </div>
              </div>
              <div class="box-body" v-if="loading">
                <div class="progress progress-sm active">
                  <div
                    class="progress-bar progress-bar-success progress-bar-striped"
                    role="progressbar"
                    :aria-valuenow="percentCompleted"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    :style="'width: ' + percentCompleted + '%'"
                  ></div>
                </div>
                <span>{{percentCompleted}}% Complete</span>
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
    ...mapGetters("PagesSingle", [
      "item",
      "loading",
      "percentCompleted",
      "axiosCancel"
    ])
  },
  created() {
    this.init();
  },
  destroyed() {
    if (this.axiosCancel != null) {
      this.axiosCancel();
    }
    this.resetState();
    // AxiosSource.cancel("Operation canceled by the user.");
  },
  watch: {
    "$route.params.id": function() {
      this.init();
    }
  },
  methods: {
    ...mapActions("PagesSingle", [
      "fetchData",
      "updateData",
      "resetState",
      "setImage"
    ]),
    init() {
      this.resetState();
      this.fetchData(this.$route.params.id).then(() => {
        this.image = this.item.img_url;
      });
    },
    submitForm() {
      this.setImage(this.image);
      this.updateData()
        .then(() => {
          this.$router.push({
            name: "pages.index",
            params: { id: this.item.book_id }
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
