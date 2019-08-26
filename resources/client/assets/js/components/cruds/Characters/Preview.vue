<template>
  <div class="row apearence ap_apearence" :id="'character' + character.id">
    <div class="form-group">
      <div class="radio">
        <label>
          <input type="radio" value="chico" v-model="gender" checked />
          Male
        </label>
        <label>
          <input type="radio" value="chica" v-model="gender" />
          Female
        </label>
      </div>
      <label for="name">Enter Name</label>
      <input
        type="text"
        class="form-control"
        name="name"
        placeholder="Enter name"
        ref="name"
        v-model="result['name'][1]"
        style="width:90%"
      />
    </div>
    <div class="panel" :id="'panel' + character.id">
      <img class="preloader" src="/gif/loading1.gif" style="width:80px" v-show="!imgUrl" />
      <vue-load-image v-if="imgUrl">
        <img slot="image" class="img-responsive" :src="imgUrl" alt />
        <img slot="preloader" class="preloader" src="/gif/loading1.gif" style="width:80px" />
        <div slot="error">Error</div>
      </vue-load-image>
    </div>
    <div class="controls">
      <div class="controlbox" :id="'controlbox' + character.id"></div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from "vuex";
export default {
  props: ["character"],
  data() {
    return {
      gender: "chico",
      json: null,
      result: {
        id: "",
        gender: "chico",
        name: ["", ""],
        color: {},
        show: {},
        layers: {}
      },
      bookId: this.$route.params.id,
      loadingImage: false,
      imgUrl: this.character.thumb_url
    };
  },
  mounted() {
    //draw control box
    this.json = JSON.parse(this.character.json).modelo;
    this.result["id"] = this.json.id;
    this.result["name"][0] = this.json.name;
    this.drawControlBox();
    this.initAction();
    this.initialClick();
    this.$eventHub.$on("previewAllPages", this.setCustomize);
  },
  watch: {
    gender: function(val) {
      this.result["gender"] = val;
      this.drawControlBox();
      this.initialClick();
      this.chageAvatar();
    }
    // result: {
    //   handler: function(val) {
    //     this.$emit("update:result", val);
    //     console.log(val);
    //   },
    //   deep: true
    // }
  },
  methods: {
    ...mapActions("BooksSingle", ["updateCustomize"]),
    ...mapActions("CharactersSingle", ["applySVG"]),
    chageAvatar() {
      var param = window.btoa(JSON.stringify(this.result));
      this.imgUrl = "/avatar?id=" + this.character.id + "&param=" + param;

      // if (this.loadingImage) {
      //   return;
      // }
      // this.loadingImage = true;

      // this.applySVG({
      //   id: this.character.id,
      //   json: this.result
      // })
      //   .then(data => {
      //     //console.log(data);
      //     this.imgUrl = data + "?t=" + new Date().getTime();
      //   })
      //   .finally(() => {
      //     this.loadingImage = false;
      //   });
    },
    setCustomize() {
      console.log(this.result);
      //this.imgUrl = null;

      this.updateCustomize({
        id: this.character.id,
        result: this.result
      });
    },
    drawControlBox() {
      var cId = this.character.id;
      var json = this.json;
      var gender = this.gender;
      var xml = '<?xml version="1.0" encoding="utf-8"?>';
      var viewBox;
      var parent = this;
      var i;
      $("#controlbox" + cId).html("");
      var layer_path =
        "/uploads/book/" +
        this.bookId +
        "/layer_json/" +
        this.character.id +
        "/" +
        gender +
        "/";
      if (typeof json[gender] != "undefined") {
        $.each(json[gender], function(ii, i) {
          parent.result["layers"][ii] = {
            show: "",
            color: ""
          };
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
                // if (u[0] != "") {
                if (iu == 0) {
                  parent.result["color"][o.target] = [u[0], u[1]];
                  parent.result["layers"][ii]["color"] = {
                    target: o.target,
                    fill: u[0],
                    stroke: u[1]
                  };
                }
                box +=
                  '<span class="configOpt configColor ' +
                  (iu == 0 ? "choosen" : "") +
                  '" data-target="' +
                  ii +
                  '" data-id="' +
                  u[0] +
                  '" data-color-target="' +
                  o.target +
                  '" data-stroke="' +
                  u[1] +
                  '" style="background: #' +
                  u[0] +
                  ";border-color: #" +
                  u[1] +
                  '";></span>';
                // }
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
                for (var i in o.value) {
                  if (i == 0) {
                    parent.result["show"][o.target] = [o.value[i][0]];
                    parent.result["layers"][ii]["show"] = o.value[i][0];
                  }
                  var img_url = layer_path + o.value[i][0] + ".png";
                  box +=
                    '<span class="configOpt configHide ' +
                    (i == 0 ? "choosen" : "") +
                    '" data-target="' +
                    ii +
                    '" data-filtro="' +
                    o.extra +
                    '" data-id="' +
                    o.value[i][0] +
                    '">' +
                    "<img width='100%' height='100%' src=" +
                    img_url +
                    " />" +
                    "</span>";
                }
                box +=
                  '<span class="configOpt configHide ' +
                  '" data-target="' +
                  ii +
                  '" data-filtro="' +
                  o.extra +
                  '" data-id="' +
                  o.value[i][0] +
                  "xxx" +
                  '">' +
                  "None" +
                  "</span>";
              }
              // if (
              //   o.target != "" &&
              //   o.target != "..." &&
              //   o.extra == "Sin opciones"
              // ) {
              //   var html = $("#panel" + cId)
              //     .find("[id^='" + gender + "']")
              //     .find("#" + o.target)
              //     .html();
              //   box +=
              //     '<span class="configOpt configHide" data-target="' +
              //     o.target +
              //     '" data-filtro="1" data-id="1">' +
              //     xml +
              //     '<svg class="mini" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="' +
              //     viewBox +
              //     '">' +
              //     html +
              //     "</svg></span>";
              //   box +=
              //     '<span class="configOpt configHide" data-target="' +
              //     o.target +
              //     '" data-filtro="0" data-id="0" style="background:#DDD;"><i class="fas fa-ban" style="font-size: xx-large; line-height: 80px; color: red;"></i></span>';
              // }
            }

            box += "</div>";
            sub++;
          });
          box += "</div>";
          $("#controlbox" + cId).append(box);
        });
      }
      var len = $("#controlbox" + cId).find(".grupo_control").length;
      $("#controlbox" + cId).css("width", len + "00%");
      $("#controlbox" + cId)
        .find(".grupo_control")
        .css("width", 100 / len + "%");
    },
    initAction() {
      var cId = this.character.id;
      var parent = this;
      $("#controlbox" + cId).on("click", ".configColor", function() {
        if (parent.loadingImage) {
          return;
        }
        var id = $(this).attr("data-id");
        var target = $(this).attr("data-target");
        var stroke = $(this).attr("data-stroke");
        var colorTarget = $(this).attr("data-color-target");
        $(this)
          .siblings(".choosen")
          .removeClass("choosen");
        $(this).addClass("choosen");
        parent.result["color"][target] = [id, stroke];
        parent.result["layers"][target]["color"] = {
          target: colorTarget,
          fill: id,
          stroke: stroke
        };
        parent.chageAvatar();
        // $("input[name=character_info]").val(JSON.stringify(result));
      });
      $("#controlbox" + cId).on("click", ".configHide", function() {
        if (parent.loadingImage) {
          return;
        }
        var target = $(this).attr("data-target");
        var filtro = $(this).attr("data-filtro");
        var id = $(this).attr("data-id");

        $(this)
          .siblings(".choosen")
          .removeClass("choosen");
        $(this).addClass("choosen");
        parent.result["show"][target] = [id];
        parent.result["layers"][target]["show"] = id;
        parent.chageAvatar();
        //$("input[name=character_info]").val(JSON.stringify(result));
      });
      $("#controlbox" + cId).on("click", ".control_right", function() {
        var a = $(this)
          .parent()
          .parent()
          .prevAll().length;
        var d = $(this)
          .parent()
          .parent()
          .nextAll().length;
        if (d > 0) {
          $("#controlbox" + cId).css("margin-left", "-" + (a + 1) * 100 + "%");
        }
      });
      $("#controlbox" + cId).on("click", ".control_left", function() {
        var a = $(this)
          .parent()
          .parent()
          .prevAll().length;
        if (a > 0) {
          $("#controlbox" + cId).css("margin-left", (a - 1) * -100 + "%");
        }
      });
      $("#controlbox" + cId).on("click", ".subtitle", function() {
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
      var cId = this.character.id;
      $("#controlbox" + cId)
        .find(".subheader")
        .find(".subtitle:first")
        .click();
      this.chageAvatar();
    }
  }
};
</script>


