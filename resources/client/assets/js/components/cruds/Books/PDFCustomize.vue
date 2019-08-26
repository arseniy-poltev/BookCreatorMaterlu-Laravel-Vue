<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>PDF Customize</h1>
      <back-buttton></back-buttton>
    </section>
    <section class="content">
      <div class="row" v-if="loading">
        <div class="col-xs-4 col-xs-offset-4">
          <div class="alert text-center">
            <i class="fa fa-spin fa-refresh"></i> Loading
          </div>
        </div>
      </div>
      <div class="row" v-if="!loading">
        <div class="col-md-12">
          <img :src="item.img_url" class="book-logo" />
          <span class="book-title">{{item.name}}</span>
        </div>
        <div class="col-md-5">
          <div class="form-group">
            <label for="language">Language of the story</label>
            <select class="form-control" name="language" required v-model="language">
              <option value="es">Spanish</option>
              <option value="en">English</option>
              <option value="fr">French</option>
            </select>
          </div>
          <div class="form-group">
            <label for="font-style">Font Style</label>
            <div class="radio">
              <label>
                <input type="radio" value="standard" checked v-model="fontStyle" />
                Standard
              </label>
              <label>
                <input type="radio" value="uppercase" v-model="fontStyle" />
                UPPERCASE
              </label>
            </div>
          </div>
        </div>
        <div class="col-md-2" style="display:grid">
          <button
            type="button"
            class="btn btn-default btn-lg"
            @click="makePDF('web')"
            :disabled="procPDF['web']"
          >
            <i class="fa fa-spin fa-refresh" v-if="procPDF['web']"></i>
            Web PDF
          </button>
          <a :href="'/uploads/book/' + bookId + '/web.pdf'" target="_blank">View WebPDF</a>
          <button type="button" class="btn btn-default btn-lg" @click="previewBook">Preview</button>
          <!-- <a
            :href="'/uploads/book/' + bookId + '/print.pdf'"
            :download="item.name + '_print'"
          >Download Print PDF</a>-->
        </div>
      </div>
      <div class="characters-part" v-if="!loading">
        <character-preview
          v-for="(character, index) in characters"
          v-bind:key="index"
          :character="character"
        ></character-preview>
      </div>
      <div class="row" v-if="!procPDF['web']">
        <div class="book-viewer-container">
          <div class="book-viewer cover">
            <div
              class="page"
              v-for="(page,index) in pages"
              v-bind:key="index"
              :page-number="index"
              :style="{zIndex: index%2==0?100-index:index}"
            >
              <img v-if="page.content" class="img" :src="page.content" />
              <img
                v-if="!page.content"
                class="preloader"
                src="/gif/loading4.gif"
                style="width:50px;"
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
import CharacterPreview from "../Characters/Preview";
import BookBlock from "vue-bookblock";
function changeExtension(str, oldExt, newExt) {
  return str.replace(oldExt, newExt);
}
export default {
  data() {
    return {
      // Code...
      bookId: this.$route.params.id,
      language: "es",
      fontStyle: "standard",
      pageNo: -1,
      procPDF: {
        web: false,
        print: false
      },
      currentTurning: -1,
      stopNextAction: false,
      stopPrevAction: false
    };
  },
  computed: {
    ...mapGetters("BooksSingle", ["characters", "loading", "pages", "item"])
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
    "character-preview": CharacterPreview,
    "book-block": BookBlock
  },
  methods: {
    ...mapActions("BooksSingle", [
      "fetchCharactersInfo",
      "resetState",
      "setParamJSON",
      "fetchData",
      "generatePDF",
      "fetchPagesSVG",
      "applyPageSVG",
      "resetPageContent"
    ]),
    previewBook() {
      this.pageNo = 0;
      $(".turned").removeClass("turned");
      $(".book-viewer").removeClass("backcover");
      $(".book-viewer").addClass("cover");
      this.resetPageContent();
      this.$eventHub.$emit("previewAllPages");
      var json = [];
      for (var i in this.characters) {
        var result = this.characters[i]["result"];
        result["id"] = this.characters.length == 1 ? "" : Number(i) + 1;
        json.push(result);
      }
      this.setParamJSON(JSON.stringify(json));

      this.loadPage(0);
      //this.applyPageSVG(this.pageNo++);
      //this.applyPageSVG(this.pageNo++);
    },
    loadPage(index) {
      if (index >= this.pages.length) {
        return;
      }
      this.applyPageSVG(index).then(() => {
        this.loadPage(index + 1);
      });
    },
    initPageAction() {
      var parent = this;
      $(".page:nth-child(odd)").on("click", function() {
        $(this)
          .parent()
          .removeClass("cover");
        $(this).addClass("turned");
        $(this)
          .next()
          .addClass("turned");
        if (
          $(this)
            .next()
            .next().length == 0
        ) {
          $(this)
            .parent()
            .addClass("backcover");
        }
        if (parent.pageNo >= parent.pages.length) {
          return;
        }
      });
      $(".page:nth-child(even)").on("click", function() {
        $(this)
          .parent()
          .removeClass("backcover");
        $(this)
          .prev()
          .removeClass("turned");
        $(this).removeClass("turned");
        if (
          $(this)
            .prev()
            .prev().length == 0
        ) {
          $(this)
            .parent()
            .addClass("cover");
        }
      });
    },
    makePDF(type) {
      this.$eventHub.$emit("previewAllPages");
      var json = [];
      for (var i in this.characters) {
        var result = this.characters[i]["result"];
        result["id"] = this.characters.length == 1 ? "" : Number(i) + 1;
        json.push(result);
      }

      this.procPDF[type] = true;
      var param = {
        type: type,
        language: this.language,
        fontStyle: this.fontStyle,
        characters: JSON.stringify(json)
      };
      this.generatePDF(param)
        .then(() => {})
        .catch(error => {
          alert(error);
        })
        .finally(() => {
          this.procPDF[type] = false;
        });
    },
    init() {
      this.bookId = this.$route.params.id;
      this.fetchData(this.bookId).then(() => {
        this.fetchCharactersInfo(this.bookId)
          .then(() => {
            //draw svg and controllers
          })
          .catch(error => {
            console.log(error);
          });
      });
      this.fetchPagesSVG(this.$route.params.id).then(() => {
        var parent = this;
        setTimeout(function() {
          parent.initPageAction();
        }, 100);
      });
    }
  }
};
</script>
<style>
@import "../../../../css/book_preview.css";
.book-logo {
  max-width: 150px;
}
.book-title {
  vertical-align: middle;
  font-size: 30px;
  color: #0b342e;
  font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
    "Lucida Sans", Arial, sans-serif;
}
</style>