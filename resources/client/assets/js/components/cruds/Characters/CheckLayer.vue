<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <back-buttton></back-buttton>
      <!-- <span>Layer Check</span> -->
    </section>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <div class="form-group">
                <div class="radio">
                  <label class="large-label">
                    <input
                      class="large-radio"
                      type="radio"
                      name="gender"
                      value="chico"
                      v-model="gender"
                      checked
                    />
                    Male
                  </label>
                  <label class="large-label">
                    <input
                      class="large-radio"
                      type="radio"
                      name="gender"
                      value="chica"
                      v-model="gender"
                    />
                    Female
                  </label>
                </div>
              </div>
            </div>

            <div class="box-body">
              <!-- <router-link
                v-if="$can('book_access')"
                :to="{ name: 'books.index'}"
                class="btn btn-default btn-sm"
              >
                <span class="glyphicon glyphicon-chevron-left"></span> Back to all Books
              </router-link>-->
            </div>
            <div class="row" v-if="loading || loadingPage">
              <div class="col-xs-4 col-xs-offset-4">
                <div class="alert text-center">
                  <i class="fa fa-spin fa-refresh"></i> Loading
                </div>
              </div>
            </div>
            <bootstrap-alert />
            <div v-if="!loading && !loadingPage">
              <div class="box-body">
                <div class="row" v-if="loadingPage">
                  <div class="col-xs-4 col-xs-offset-4">
                    <div class="alert text-center">
                      <i class="fa fa-spin fa-refresh"></i> Loading
                    </div>
                  </div>
                </div>
                <div class="row apearence ap_apearence">
                  <div class="panel" v-html="item.svg"></div>
                  <div class="controls">
                    <div class="controlbox"></div>
                  </div>
                </div>
                <div class="row">
                  <div v-if="!loadingPage" class="page-panel">
                    <div
                      class="gallery-image"
                      v-for="(item, imageIndex) in pages"
                      :key="imageIndex"
                      @click="index = imageIndex"
                      :style="{ width: '150px', height: '150px' }"
                    >
                      <page-status :file_name="item.file_name" :status="item.status"></page-status>
                      <div v-html="item.svg"></div>
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
import VueGallery from "vue-gallery";
export default {
  data() {
    return {
      // Code...
      characterId: this.$route.params.id,
      gender: "chico",
      pages: [],
      loadingPage: false,
      index: null
    };
  },
  computed: {
    ...mapGetters("CharactersSingle", ["item", "loading"])
  },
  created() {
    this.fetchPages();
  },
  destroyed() {
    this.resetState();
  },
  watch: {
    "$route.params.id": function() {
      this.resetState();
      this.fetchCharacterSVG(this.$route.params.id);
    },
    gender: function() {
      this.drawControls(this.item.json.modelo);
      this.initialClick();
    }
  },
  components: {
    gallery: VueGallery
  },
  methods: {
    ...mapActions("CharactersSingle", [
      "fetchCharacterJSON",
      "fetchCharacterSVG",
      "resetState"
    ]),
    fetchPages() {
      //this.loadingPage = true;

      //axios
      //.get("/api/v1/get-pages-svg/" + this.characterId)
      //.then(response => {
      //this.pages = response.data.data;
      this.fetchCharacterSVG(this.characterId).then(() => {
        this.fetchCharacterJSON(this.characterId).then(() => {
          this.drawControls(this.item.json.modelo);
          this.initAction();
          this.initialClick();
        });
      });
      //})
      // .catch(error => {
      //   message = error.response.data.message || error.message;
      //   console.log(message);
      // })
      // .finally(() => {
      //   this.loadingPage = false;
      // });
    },
    initAction() {
      $("body").on("click", ".configColor", function() {
        var id = $(this).attr("data-id");
        var target = $(this).attr("data-target");
        var stroke = $(this).attr("data-stroke");
        $(this)
          .siblings(".choosen")
          .removeClass("choosen");
        $(this).addClass("choosen");
        $("svg")
          .find("[id^='" + target + "']")
          .css("fill", "#" + id);
        if (stroke != "") {
          $("svg")
            .find("[id^='" + target + "']")
            .css("stroke", "#" + $(this).attr("data-stroke"));
        }
        // result["color"][target] = [id, stroke];
        // $("input[name=character_info]").val(JSON.stringify(result));
      });
      $("body").on("click", ".configHide", function() {
        var target = $(this).attr("data-target");
        var filtro = $(this).attr("data-filtro");
        var id = $(this).attr("data-id");

        $(this)
          .siblings(".choosen")
          .removeClass("choosen");
        $(this).addClass("choosen");
        if (id == 1 || id == 0) {
          if (id == 1) {
            $("svg")
              .find("[id^='" + target + "']")
              .show();
          } else {
            $("svg")
              .find("[id^='" + target + "']")
              .hide();
          }
        } else {
          $("svg")
            .find("[id^='" + target + "']")
            .find("[id^='" + filtro + "']")
            .hide();
          $("svg")
            .find("[id^='" + target + "']")
            .find("[id^='" + id + "']:first")
            .show();
        }
        //result["show"][target] = [id];
        //$("input[name=character_info]").val(JSON.stringify(result));
      });
      $("body").on("click", ".control_right", function() {
        var a = $(this)
          .parent()
          .parent()
          .prevAll().length;
        var d = $(this)
          .parent()
          .parent()
          .nextAll().length;
        if (d > 0) {
          $(".controlbox").css("margin-left", "-" + (a + 1) * 100 + "%");
        }
      });
      $("body").on("click", ".control_left", function() {
        var a = $(this)
          .parent()
          .parent()
          .prevAll().length;
        if (a > 0) {
          $(".controlbox").css("margin-left", (a - 1) * -100 + "%");
        }
      });
      $("body").on("click", ".subtitle", function() {
        var a = $(this).attr("data-id");
        $(this)
          .parent()
          .nextAll(".body_control")
          .addClass("hide");
        $(this)
          .parent()
          .nextAll(".body_control" + a)
          .removeClass("hide");
      });
    },
    initialClick() {
      $(".body_control")
        .find(".configOpt:first")
        .click();
      $(".subheader")
        .find(".subtitle:first")
        .click();
    },
    drawControls(json) {
      var gender = this.gender;
      var xml = '<?xml version="1.0" encoding="utf-8"?>';
      var viewBox;
      var i;
      for (i = 0; i < $(".panel").find("svg")[0].attributes.length; i++) {
        if ($(".panel").find("svg")[0].attributes[i].name == "viewBox") {
          viewBox = $(".panel").find("svg")[0].attributes[i].value;
        }
      }
      $("svg")
        .find("[id^='chico']")
        .hide();
      $("svg")
        .find("[id^='chica']")
        .hide();
      $("svg")
        .find("[id^='" + gender + "']")
        .show();
      $(".controlbox").html("");

      if (typeof json[gender] != "undefined") {
        $.each(json[gender], function(ii, i) {
          var box =
            '<div class="grupo_control"><div class="header_control"><div class="boton_control control_left"><i class="fa fa-angle-left"></i></div><div class="title_control">' +
            ii +
            '</div><div class="boton_control control_right"><i class="fa fa-angle-right"></i></div></div>';

          var sub = 0;
          var reduced = "";
          var hide;

          if (i.length > 1) {
            box +=
              '<div class="header_control subheader"><div class="title_control subtitle" data-id="0">' +
              "Color" +
              '</div><div class="title_control subtitle" data-id="1">' +
              "Shape" +
              "</div></div>";
            reduced = "reduced";
          }

          $.each(i, function(io, o) {
            if (sub > 0) hide = "hide";
            box +=
              '<div class="body_control ' +
              reduced +
              " body_control" +
              sub +
              " " +
              '">';

            if (o.tipo == "Color") {
              $.each(o.value, function(iu, u) {
                if (u[0] != "") {
                  box +=
                    '<span class="configOpt configColor" data-target="' +
                    o.target +
                    '" data-id="' +
                    u[0] +
                    '" data-stroke="' +
                    u[1] +
                    '" style="background: #' +
                    u[0] +
                    ';"></span>';
                }
              });
            }
            if (o.tipo == "Mostrar/Ocultar") {
              if (
                o.extra != "" &&
                o.target != "" &&
                o.extra != "..." &&
                o.target != "..." &&
                o.extra != "Sin opciones"
              ) {
                $(".panel")
                  .find("[id^='" + gender + "']")
                  .find("[id^='" + o.target + "']")
                  .children("[id^='" + o.extra + "']")
                  .each(function(iu, u) {
                    var html = $(this).html();
                    var id = $(this).attr("id");
                    box +=
                      '<span class="configOpt configHide" data-target="' +
                      o.target +
                      '" data-filtro="' +
                      o.extra +
                      '" data-id="' +
                      id +
                      '">' +
                      xml +
                      '<svg class="mini" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="' +
                      viewBox +
                      '">' +
                      html +
                      "</svg></span>";
                  });
              }
              if (
                o.target != "" &&
                o.target != "..." &&
                o.extra == "Sin opciones"
              ) {
                var html = $(".panel")
                  .find("[id^='" + gender + "']")
                  .find("#" + o.target)
                  .html();
                box +=
                  '<span class="configOpt configHide" data-target="' +
                  o.target +
                  '" data-filtro="1" data-id="1">' +
                  xml +
                  '<svg class="mini" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="' +
                  viewBox +
                  '">' +
                  html +
                  "</svg></span>";
                box +=
                  '<span class="configOpt configHide" data-target="' +
                  o.target +
                  '" data-filtro="0" data-id="0" style="background:#DDD;"><i class="fas fa-ban" style="font-size: xx-large; line-height: 80px; color: red;"></i></span>';
              }
            }

            box += "</div>";
            sub++;
          });
          box += "</div>";
          $(".controlbox").append(box);
        });
      }
      var len = $(".grupo_control").length;
      $(".controlbox").css("width", len + "00%");
      $(".grupo_control").css("width", 100 / len + "%");
    }
  }
};
</script>
<style>
@import "../../../../css/book_preview.css";
</style>


