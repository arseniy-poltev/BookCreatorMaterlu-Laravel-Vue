<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Pages</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">List</h3>
            </div>
            <div class="box-body">
              <back-buttton></back-buttton>
              <!-- <router-link
                v-if="$can('book_view')"
                :to="{ name: 'books.show', params: { id: bookId }}"
                class="btn btn-default btn-sm"
              >
                <span class="glyphicon glyphicon-chevron-left"></span> Back to Book
              </router-link>-->
            </div>
            <div class="box-body">
              <div class="btn-group">
                <router-link
                  v-if="$can(xprops.permission_prefix + 'create')"
                  :to="{ name: xprops.route + '.create' }"
                  class="btn btn-success btn-sm"
                >
                  <i class="fa fa-plus"></i> Add new
                </router-link>

                <button type="button" class="btn btn-default btn-sm" @click="init">
                  <i class="fa fa-refresh" :class="{'fa-spin': loading}"></i> Refresh
                </button>
                <router-link :to="{ name: xprops.route + '.sort' }" class="btn btn-danger btn-sm">
                  <i class="fa fa-sort"></i> Sort Pages
                </router-link>
              </div>
            </div>

            <div class="box-body">
              <div class="row" v-if="loading">
                <div class="col-xs-4 col-xs-offset-4">
                  <div class="alert text-center">
                    <i class="fa fa-spin fa-refresh"></i> Loading
                  </div>
                </div>
              </div>

              <!-- <datatable
                v-if="!loading"
                :columns="columns"
                :data="data"
                :total="total"
                :query="query"
                :xprops="xprops"
              />-->
              <div v-if="!loading">
                <div class="row">
                  <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green">
                      <div class="inner">
                        <h3>{{getStatusCnt('correct')}}</h3>
                        <h4>Correct Pages</h4>
                      </div>
                      <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-red">
                      <div class="inner">
                        <h3>{{getStatusCnt('incorrect')}}</h3>
                        <h4>Incorrect Pages</h4>
                      </div>
                      <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                      </div>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-yellow">
                      <div class="inner">
                        <h3>{{getStatusCnt('not_check')}}</h3>
                        <h4>Not Checked Pages</h4>
                      </div>
                      <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                      </div>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-aqua">
                      <div class="inner">
                        <h3>{{total}}</h3>
                        <h4>All Pages</h4>
                      </div>
                      <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                      </div>
                    </div>
                  </div>
                  <!-- ./col -->
                </div>
                <gallery :images="images" :index="index" @close="index = null"></gallery>
                <vue-load-image
                  v-for="(item, imageIndex) in data"
                  :key="imageIndex"
                  class="image-wrapper"
                >
                  <div
                    slot="image"
                    :data-src="item.thumb_url"
                    @click.self="index = imageIndex"
                    class="image"
                    :style="{ backgroundImage: 'url(' + item.thumb_url + ')'}"
                  >
                    <page-status :file_name="item.file_name" :status="item.status"></page-status>
                    <div class="btn-group action-group">
                      <button
                        type="button"
                        class="btn btn-default"
                        v-on:click="destroyData(item.id)"
                      >
                        <i class="fa fa-trash"></i>
                      </button>
                      <router-link
                        v-if="$can(xprops.permission_prefix + 'edit')"
                        :to="{ name: xprops.route + '.edit', params: { id: item.id } }"
                        class="btn btn-default"
                      >
                        <i class="fa fa-edit"></i>
                      </router-link>
                      <router-link
                        v-if="$can(xprops.permission_prefix + 'edit')"
                        :to="{ name: xprops.route + '.layer_check', params: { id: item.id } }"
                        class="btn btn-default"
                      >
                        <i class="fa fa-check"></i>
                      </router-link>
                    </div>
                  </div>
                  <div slot="error">error</div>

                  <img slot="preloader" class="preloader" src="/gif/loading.gif" style="width:50px" />
                </vue-load-image>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>


<script>
import { mapGetters, mapActions } from "vuex";
import DatatableActions from "../../dtmodules/DatatableActions";
import DatatableImageView from "../../dtmodules/DatatableImageView";
import VueGallery from "vue-gallery";

export default {
  data() {
    return {
      columns: [
        { title: "#", field: "id", sortable: true, colStyle: "width: 50px;" },
        {
          title: "Image",
          tdComp: DatatableImageView,
          colStyle: "width: 280px;"
        },
        {
          title: "Actions",
          tdComp: DatatableActions,
          visible: true,
          thClass: "text-right",
          tdClass: "text-right",
          colStyle: "width: 130px;"
        }
      ],
      query: { sort: "id", order: "desc" },
      xprops: {
        module: "PagesIndex",
        route: "pages",
        permission_prefix: "page_"
      },
      index: null,
      bookId: this.$route.params.id
    };
  },
  components: {
    gallery: VueGallery
  },
  mounted() {
    this.init();
  },
  destroyed() {
    this.resetState();
  },
  computed: {
    ...mapGetters("PagesIndex", [
      "data",
      "images",
      "total",
      "loading",
      "relationships"
    ])
  },
  watch: {
    "$route.params": function() {
      this.init();
    },
    query: {
      handler(query) {
        this.setQuery(query);
      },
      deep: true
    }
  },
  methods: {
    ...mapActions("PagesIndex", ["fetchData", "setQuery", "resetState"]),
    getStatusCnt(status) {
      return this.data.filter(function(item) {
        return item.status == status;
      }).length;
    },
    init() {
      this.statusSet = false;
      this.statusArr = [];
      this.fetchData(this.$route.params.id).then(() => {});
    },

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
            .dispatch(this.xprops.module + "/destroyData", id)
            .then(result => {
              this.$eventHub.$emit("delete-success");
            });
        }
      });
    }
  }
};
</script>


<style scoped>
.image {
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center center;
  width: 300px;
  height: 300px;
}
.image-wrapper {
  float: left;
  border: 1px solid #ebebeb;
  margin: 10px;
  padding: 5px;
  border-radius: 5px;
  cursor: -moz-zoom-in;
  cursor: -webkit-zoom-in;
  cursor: zoom-in;
  display: flex;
  width: 300px;
  height: 300px;
  position: relative;
}
.preloader {
  margin: auto;

  display: block;
}
.action-group {
  position: absolute;
  bottom: 0;
  right: 0;
}
</style> 