<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Books</h1>
      <back-buttton></back-buttton>
    </section>
    <section class="content">
      <div class="row" v-if="loading">
        <div class="col-xs-4 col-xs-offset-4">
          <div class="alert text-center">
            <i class="fa fa-spin fa-refresh"></i> Loading Characters
          </div>
        </div>
      </div>
      <div class="characters-part" v-if="!loading">
        <character-preview
          v-for="(character, index) in characters"
          v-bind:key="index"
          :character="character"
        ></character-preview>
      </div>
      <div class="row">
        <div class="row" v-if="loadingPage">
          <div class="col-xs-4 col-xs-offset-4">
            <div class="alert text-center">
              <i class="fa fa-spin fa-refresh"></i> Loading Pages
            </div>
          </div>
        </div>
        <div class="row">
          <i class="fa fa-spin fa-refresh" v-if="processing"></i>
          <button
            type="button"
            class="btn btn-danger btn-lg btn-preview"
            @click="previewPages"
            v-if="!loadingPage"
            :disabled="processing"
          >Preview All Pages</button>
        </div>

        <div v-if="!loadingPage" class="page-panel">
          <div
            class="gallery-image"
            v-for="(item, imageIndex) in pages"
            :key="imageIndex"
            @click="index = imageIndex"
            :style="{ width: '350px', height: '350px'}"
          >
            <!-- {{item.loading}} -->

            <page-status :file_name="item.file_name" :status="item.status"></page-status>
            <button type="button" class="btn btn-success btn-reload" :disabled="item.loading">load</button>
            <img
              class="preloader"
              src="/gif/loading4.gif"
              style="width:50px;"
              v-show="item.loading"
            />
            <div>
              <vue-load-image v-if="!item.loading">
                <img slot="image" class="img-responsive" :src="item.img_path" alt />
                <img slot="preloader" class="preloader" src="/gif/loading4.gif" style="width:50px;" />
                <div slot="error">Error</div>
              </vue-load-image>
            </div>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import VueGallery from "vue-gallery";
import CharacterPreview from "../Characters/Preview";

function soloLetras(str) {
  var regex = /[^a-zA-Z].*/gm;
  var subst = "";
  return str.replace(regex, subst);
}
export default {
  data() {
    return {
      // Code...
      bookId: this.$route.params.id,
      index: null,
      processing: false
    };
  },
  computed: {
    ...mapGetters("BooksSingle", [
      "characters",
      "pages",
      "loading",
      "loadingPage"
    ])
  },
  mounted() {
    this.init();
  },
  destroyed() {
    this.resetState();
  },
  watch: {
    "$route.params.id": function() {
      this.resetState();
      this.init();
    }
  },
  components: {
    gallery: VueGallery,
    "character-preview": CharacterPreview
  },
  methods: {
    ...mapActions("BooksSingle", [
      "fetchCharactersInfo",
      "fetchPagesSVG",
      "resetState",
      "setPageImgPath",
      "setPageLoading",
      "setParamJSON",
      "applyPageSVG"
    ]),
    previewPages() {
      this.$eventHub.$emit("previewAllPages");
      var json = [];
      for (var i in this.characters) {
        var result = this.characters[i]["result"];
        result["id"] = this.characters.length == 1 ? "" : Number(i) + 1;
        json.push(result);
      }
      this.setParamJSON(JSON.stringify(json));
      this.loadPageImage(0, true);

      /*
      var cnt = 0;
      var parent = this;
      this.processing = true;
      for (var i in this.pages) {
        this.applyPageSVG(i).then(() => {
          cnt++;
          if (cnt == parent.pages.length) {
            parent.processing = false;
          }
        });
      }
      */
    },
    loadPageImage(index, flag) {
      this.processing = true;
      if (index == this.pages.length) {
        this.processing = false;
        return;
      }
      var parent = this;
      this.applyPageSVG(index).then(() => {
        //this.processing = false;
        setTimeout(function() {
          parent.loadPageImage(index + 1, flag);
        }, 10);
      });
    },

    init() {
      this.fetchCharactersInfo(this.$route.params.id)
        .then(() => {
          //draw svg and controllers
        })
        .catch(error => {
          console.log(error);
        });
      this.fetchPagesSVG(this.$route.params.id).then(() => {});
    }
  }
};
</script>
<style>
@import "../../../../css/book_preview.css";
</style>
<style scoped>
.btn-reload {
  position: absolute;
  margin-top: 30px;
}
</style>
