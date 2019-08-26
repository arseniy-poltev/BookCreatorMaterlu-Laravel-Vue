<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Page</h1>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Check Layer</h3>
            </div>
            <div class="box-body">
              <back-buttton></back-buttton>
              <!-- <router-link
                v-if="item.book_id > 0&&$can('page_access')"
                :to="{ name: 'pages.index',params: { id: item.book_id}}"
                class="btn btn-default btn-sm"
              >
                <span class="glyphicon glyphicon-chevron-left"></span> Back to all Pages
              </router-link>-->
              <button type="button" class="btn btn-default btn-sm" @click="init">
                <i class="fa fa-refresh" :class="{'fa-spin': loading}"></i> Refresh
              </button>
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
              <div class="col-md-12 row">
                <page-status :file_name="item.file_name" :status="item.status"></page-status>
              </div>
              <br />
              <div class="col-md-12 row">
                <div class="col-md-4 svg-panel">
                  <vue-load-image class="image-wrapper">
                    <img slot="image" class="img-responsive" :src="imgUrl" alt />
                    <img
                      slot="preloader"
                      class="preloader"
                      src="/gif/loading.gif"
                      style="width:50px;"
                    />
                    <div slot="error">Error</div>
                  </vue-load-image>
                </div>
              </div>
              <!-- <button type="button" class="btn btn-primary btn-lg" @click="checkLayers">
                <i class="fa fa-check"></i> Check Layers
              </button>-->
              <button type="button" class="btn btn-success btn-lg" @click="changeStatus('correct')">
                <i class="fa fa-save"></i> Correct
              </button>
              <button
                type="button"
                class="btn btn-danger btn-lg"
                @click="changeStatus('incorrect')"
              >
                <i class="fa fa-close"></i> Incorrect
              </button>
              <br />
              <div class="row col-md-12">
                <div
                  class="col-md-6 character-part"
                  v-for="(character,cIndex) in checkResult"
                  v-bind:key="cIndex"
                >
                  <h2>Character {{cIndex + 1}}</h2>
                  <div class="col-md-6" v-for="(gender,gIndex) in character" v-bind:key="gIndex">
                    <h3>{{gIndex}}</h3>
                    <div
                      class="row layer-part"
                      v-for="(layer, lIndex) in gender"
                      v-bind:key="lIndex"
                    >
                      <p style="font-size:20px">{{lIndex}}</p>
                      <div class="col-md-4">
                        <p class="text-aqua">In SVG</p>
                        <div
                          v-for="(option, iIndex) in layer['page']"
                          v-bind:key="'status'+iIndex"
                        >{{option}}</div>
                      </div>
                      <div class="col-md-8">
                        <p class="text-aqua">In JSON</p>
                        <div v-for="(option, vIndex) in layer['json']" v-bind:key="'value'+vIndex">
                          {{option[0]}}
                          <span v-bind:class="option[1]">({{option[1]}})</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
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

export default {
  data() {
    return {
      // Code...
      pageId: this.$route.params.id,
      checkResult: [],
      imgUrl: null
    };
  },
  computed: {
    ...mapGetters("PagesSingle", ["item", "loading"])
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
    ...mapActions("PagesSingle", ["checkPage", "resetState", "updateStatus"]),
    changeStatus(status) {
      this.updateStatus({ id: this.pageId, status: status })
        .then(() => {
          // this.$router.push({
          //   name: "pages.index",
          //   params: { id: this.item.book_id }
          // });
          this.$router.go(-1);
          this.$eventHub.$emit("update-success");
        })
        .catch(error => {
          console.error(error);
        });
    },
    init() {
      this.resetState();
      this.checkPage(this.pageId).then(data => {
        this.checkResult = JSON.parse(data.result);
        this.imgUrl = data.img_url;
      });
    }
  }
};
</script>
<style scoped>
.svg-panel {
  width: 300px;
}
.character-part {
  /* margin: 2px; */
  border-style: dashed;
  border-width: 1px;
  border-color: aquamarine;
}
.layer-part {
  background-color: floralwhite;
  padding: 10px;
  margin: px;
  border-style: dotted;
  border-width: 1px;
  margin: 2px;
  font-size: 14px;
}
.found {
  color: darkgreen;
  font-size: 20px;
}
.missing {
  color: crimson;
  font-size: 20px;
}
.image {
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center center;
  width: 300px;
  height: 300px;
}
.image-wrapper {
  float: left;
  border: 2px solid #ebebeb;
  margin: 1px;
  border-radius: 5px;
  cursor: move;
  display: flex;
  width: 300px;
  height: 300px;
}
.preloader {
  margin: auto;

  display: block;
}

/* svg {
  
} */
</style>


