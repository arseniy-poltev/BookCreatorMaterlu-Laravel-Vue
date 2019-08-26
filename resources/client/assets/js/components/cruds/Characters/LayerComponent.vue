<template>
  <div class="box box-success box-solid" id="box-container">
    <div class="box-header with-border">
      <h3 class="box-title">{{title}}</h3>

      <div class="box-tools pull-right">
        <!-- <button type="button" class="btn btn-box-tool" data-widget="remove">
                    <i class="fa fa-times"></i>
        </button>-->
      </div>
    </div>

    <div class="box-body">
      <div>
        <a class="btn btn-app" @click="addLayer">
          <i class="fa fa-plus"></i> Add Layer
        </a>
        <a class="btn btn-app" @click="extractParentLayer">
          <i class="fa fa-chain"></i> Extract from SVG
        </a>
      </div>
      <div
        :id="identifier+(index+1)"
        class="layer-part"
        v-for="(layer,index) in layers"
        v-bind:key="index"
      >
        <div class="box-tools pull-right">
          <button type="button" class="btn btn-box-tool" @click="removeLayer(index)">
            <i class="fa fa-times"></i>
          </button>
        </div>
        <br />
        <br />
        <div class="callout callout-info">
          <h2>
            <i class="fa fa-book"></i>
            <!-- Layer {{index+1}} -->
            {{layer.title}}
          </h2>
        </div>
        <div class="form-group">
          <label for="name">Layer Title *</label>
          <input
            type="text"
            class="form-control"
            v-model="layer.title"
            placeholder="Enter Layer Title *"
          />
        </div>
        <button
          type="button"
          class="btn btn-default"
          @click="addOption(index)"
          :disabled="layer.options.length > 1"
        >
          <i class="fa fa-plus">Add Option</i>
        </button>
        <div class="option-part" v-for="(option,oIndex) in layer.options" v-bind:key="oIndex">
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" @click="removeOption(index, oIndex)">
              <i class="fa fa-times"></i>
            </button>
          </div>
          <br />
          <div class="form-group col-md-3">
            <label for="name">Tipo *</label>
            <!-- <input
              type="text"
              class="form-control"
              v-model="option.tipo"
              placeholder="Enter tipo *"
            />-->
            <select class="form-control" name="tipo" required v-model="option.tipo">
              <option value="Color">Color</option>
              <option value="Mostrar/Ocultar">Mostrar/Ocultar</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label for="name">Target *</label>
            <input
              type="text"
              class="form-control"
              v-model="option.target"
              placeholder="Enter target *"
            />
          </div>
          <!-- <div class="form-group">
            <label for="name">Layer Extra Type</label>
            <div class="radio">
              <label>
                <input
                  type="radio"
                  :name="identifier+index+'-'+oIndex"
                  :value="Array.isArray(option.extra)?null:option.extra"
                  v-model="option.extra"
                  checked
                />
                Value
              </label>
              <label>
                <input
                  type="radio"
                  :name="identifier+index+'-'+oIndex"
                  :value="Array.isArray(option.extra)?option.extra:[]"
                  v-model="option.extra"
                />
                Array
              </label>
            </div>
          </div>-->
          <div class="form-group col-md-3">
            <label for="name">Extra</label>
            <input
              type="text"
              class="form-control"
              v-model="option.extra"
              placeholder="Enter extra"
            />
          </div>

          <div>
            <button
              type="button"
              class="btn btn-warning"
              style="margin-bottom:5px"
              @click="addExtraValue(index, oIndex)"
            >
              <i class="fa fa-plus">Add Extra Value</i>
            </button>
            <button
              type="button"
              class="btn btn-default"
              style="margin-bottom:5px"
              :disabled="option.tipo != 'Mostrar/Ocultar'"
              @click="getLayerFromSVG(index, oIndex)"
            >Extract From SVG</button>

            <div class="form-group row" v-for="(item,eIndex) in option.value" v-bind:key="eIndex">
              <div
                class="col-md-1"
                v-if="option.tipo == 'Color'"
                :style="{ backgroundColor: (item[0] == '' ? '' : '#' + item[0]) , height: '35px', width:'35px'}"
              ></div>
              <div class="col-md-4">
                <input
                  type="text"
                  class="form-control"
                  v-model="item[0]"
                  :maxlength="option.tipo == 'Color' ? 6 : 80"
                />
              </div>
              <div
                class="col-md-1"
                v-if="option.tipo == 'Color'"
                :style="{ backgroundColor: (item[1] == '' ? '' : '#' + item[1]) , height: '35px', width:'35px'}"
              ></div>
              <div class="col-md-4" v-if="option.tipo == 'Color'">
                <input
                  type="text"
                  class="form-control"
                  v-model="item[1]"
                  :maxlength="option.tipo == 'Color' ? 6 : 80"
                />
              </div>
              <div class="col-md-2">
                <button
                  type="button"
                  class="btn btn-danger"
                  @click="removeExtraValue(index,oIndex,eIndex)"
                >
                  <i class="fa fa-minus"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /.box-body -->
  </div>
</template>
<script>
function extractLetter(str) {
  //assume str is lowercase
  var a = str.toLowerCase();
  var b = "";
  for (var i = 0; i < a.length; i++) {
    if (a[i] >= "a" && a[i] <= "z") b += a[i];
    else break;
  }
  return b;
}

function extractCommonStr(arrStr) {
  var A = arrStr.concat().sort(),
    a1 = A[0],
    a2 = A[A.length - 1],
    L = a1.length,
    i = 0;
  while (i < L && a1.charAt(i) === a2.charAt(i)) i++;
  return a1.substring(0, i);
}
export default {
  props: ["layer_data", "title", "identifier"],
  data() {
    return {
      // Code...
      layers: _.cloneDeep(this.layer_data)
    };
  },
  created() {
    console.log(this.identifier);
    // this.extractParentLayer();
    console.log("end!");
  },

  watch: {
    layers: {
      handler: function(val) {
        this.$emit("update:layer_data", val);
        console.log("changed!");
      },
      deep: true
    },
    layer_data: {
      handler: function(val) {
        this.layers = val;
      },
      deep: true
    }
  },
  methods: {
    extractParentLayer() {
      this.layers = [];
      var parent = this;
      $(".svg-panel")
        .find("[id^='" + this.identifier + "']")
        .children()
        .each(function(key, value) {
          if (!$(this).attr("id")) {
            return true;
          }
          var target = extractLetter($(this).attr("id"));
          var layerTypes = [];
          var colorTarget = "";
          $(this)
            .children("[id]")
            .each(function(i, ii) {
              if (!$(this).attr("id")) {
                return true;
              }
              var str = extractLetter($(this).attr("id"));
              if (str.startsWith("color")) {
                colorTarget = str;
              } else if (layerTypes.indexOf(str) == -1) {
                layerTypes.push(str);
              }
            });

          if (colorTarget == "") {
            $(this)
              .find("[id^='color']:first")
              .each(function() {
                colorTarget = extractLetter($(this).attr("id"));
              });
          }

          if (layerTypes.length == 0 && colorTarget != "") {
            parent.layers.push({
              title: target,
              options: [
                {
                  tipo: "Color",
                  target: colorTarget,
                  extra: "",
                  value: []
                }
              ]
            });
          }

          for (var i in layerTypes) {
            parent.layers.push({
              title: layerTypes[i],
              options: [
                {
                  tipo: "Color",
                  target: colorTarget,
                  extra: "",
                  value: []
                },
                {
                  tipo: "Mostrar/Ocultar",
                  target: target,
                  extra: layerTypes[i],
                  value: []
                }
              ]
            });
          }
        });
    },
    addLayer() {
      this.layers.push({
        title: "",
        options: []
      });
      ``;
      var parent = this;
      setTimeout(function() {
        var target = $("#" + parent.identifier + parent.layers.length);
        $("html,body").animate(
          {
            scrollTop: target.offset().top
          },
          1000
        );
      }, 100);
    },
    removeLayer(index) {
      this.layers.splice(index, 1);
    },
    addOption(layerIndex) {
      //check length! There can be 2 options at most!
      if (this.layers[layerIndex].options.length > 1) {
        return;
      }
      this.layers[layerIndex].options.push({
        tipo: "",
        target: "",
        extra: "",
        value: []
      });
    },
    removeOption(layerIndex, optionIndex) {
      this.layers[layerIndex].options.splice(optionIndex, 1);
    },
    addExtraValue(layerIndex, optionIndex) {
      if (
        this.layers[layerIndex].options[optionIndex].value == null ||
        this.layers[layerIndex].options[optionIndex].value == undefined
      ) {
        this.layers[layerIndex].options[optionIndex]["value"] = [];
      }
      this.layers[layerIndex].options[optionIndex].value.push(["", ""]);
    },
    removeExtraValue(layerIndex, optionIndex, eIndex) {
      this.layers[layerIndex].options[optionIndex].value.splice(eIndex, 1);
    },
    getLayerFromSVG(layerIndex, optionIndex) {
      var o = this.layers[layerIndex].options[optionIndex];
      this.layers[layerIndex].options[optionIndex].value = [];

      if (o.tipo == "Mostrar/Ocultar") {
        if (
          o.extra != "" &&
          o.target != "" &&
          o.extra != "..." &&
          o.target != "..." &&
          o.extra != "Sin opciones"
        ) {
          $(".svg-panel")
            .find("[id^='" + this.identifier + "']")
            .find("[id^='" + o.target + "']")
            .children("[id^='" + o.extra + "']")
            .each(function(iu, u) {
              o.value.push([$(this).attr("id"), ""]);
            });
        }
        if (o.target != "" && o.target != "..." && o.extra == "Sin opciones") {
          var id = $(".svg-panel")
            .find("[id^='" + this.identifier + "']")
            .find("#" + o.target)
            .attr("id");
          o.value.push([id, ""]);
        }
      }
    }
  }
};
</script>

<style scoped>
.layer-part {
  border: 2px;
  border-style: dashed;
  padding: 10px;
  margin: 5px;
}
.option-part {
  border: 1px;
  border-style: dotted;
  padding: 0px 10px 0px 20px;
  margin: 2px;
}
</style>
