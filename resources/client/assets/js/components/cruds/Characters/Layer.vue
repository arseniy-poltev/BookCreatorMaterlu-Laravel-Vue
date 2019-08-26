<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Character Layer Options</h1>
      <back-buttton></back-buttton>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-xs-12 col-md-6">
          <div class="form-group">
            <button type="button" class="btn btn-success btn-lg" @click="fetchJSON">
              <i class="fa fa-refresh" :class="{'fa-spin': loading}"></i> Reload
            </button>
            <button
              :disabled="!item.json_path || loading"
              type="button"
              class="btn btn-danger btn-lg"
              @click="makeJson"
            >Make JSON</button>

            <a
              type="button"
              class="btn btn-warning btn-lg"
              :href="item.json_path"
              :disabled="!item.json_path || loading"
              download
            >Download JSON</a>
          </div>
          <div class="form-group">
            <button
              type="button"
              class="btn btn-primary btn-lg"
              @click="copyMaleToFemale"
              :disabled="loading"
            >Male->Female</button>
            <button
              type="button"
              class="btn btn-primary btn-lg"
              @click="copyFemaleToMale"
              :disabled="loading"
            >Female->Male</button>
          </div>

          <div class="form-group col-md-6">
            <label for="name">Load from File</label>
            <input
              type="file"
              class="form-control"
              name="file"
              placeholder="Select JSON File"
              accept=".json"
              @input="onOpenJSONFile"
              :disabled="loading"
            />
            <br />
            <label for="name">Enter Name Syntax</label>
            <input
              type="text"
              class="form-control col-md-3"
              name="name"
              placeholder="Enter name syntax"
              v-model="nameSyntax"
              :disabled="loading"
              ref="nameSyntax"
            />
          </div>
        </div>
        <div class="col-xs-12 col-md-6">
          <div class="svg-panel" v-html="item.svg"></div>
        </div>

        <br />
        <br />
        <br />
        <div class="row" v-if="loading">
          <div class="col-xs-4 col-xs-offset-4">
            <div class="alert text-center">
              <i class="fa fa-spin fa-refresh"></i> Loading
            </div>
          </div>
        </div>
        <div class="col-xs-12" v-if="!loading">
          <div class="col-md-6">
            <LayerComponent
              title="CHICO(Male)"
              v-if="male_layers"
              v-bind:layer_data.sync="male_layers"
              identifier="chico"
            ></LayerComponent>
          </div>
          <div class="col-md-6">
            <LayerComponent
              title="CHICA(Female)"
              v-if="female_layers"
              v-bind:layer_data.sync="female_layers"
              identifier="chica"
            ></LayerComponent>
          </div>
        </div>
      </div>
    </section>
    <go-top bg-color="#404040"></go-top>
  </section>
</template>
<script>
import LayerComponent from "./LayerComponent";
import { mapGetters, mapActions } from "vuex";
import GoTop from "@inotom/vue-go-top";
export default {
  data() {
    return {
      // Code...
      male_layers: null,
      female_layers: null,
      characterId: this.$route.params.id,
      nameSyntax: null
    };
  },
  components: {
    LayerComponent,
    GoTop
  },
  computed: {
    ...mapGetters("CharactersSingle", ["item", "loading"])
  },
  created() {
    this.fetchJSON();
  },
  destroyed() {
    this.resetState();
  },
  methods: {
    ...mapActions("CharactersSingle", [
      "fetchCharacterJSON",
      "fetchCharacterSVG",
      "resetState",
      "updateJson",
      "setLoading"
    ]),
    fetchJSON() {
      this.fetchCharacterJSON(this.characterId).then(() => {
        this.fetchCharacterSVG(this.characterId).then(() => {
          this.init(this.item.json);
        });
      });
    },
    init(obj) {
      this.male_layers = [];
      this.female_layers = [];
      if (obj.modelo == null || obj.modelo == undefined) {
        return;
      }
      this.nameSyntax = obj.name;
      var values = [[obj.modelo.chico, []], [obj.modelo.chica, []]];
      for (var i in values) {
        for (var prop in values[i][0]) {
          var layer = {
            title: prop,
            options: []
          };

          for (var option in values[i][0][prop]) {
            layer.options.push(values[i][0][prop][option]);
          }
          values[i][1].push(layer);
        }
      }
      this.male_layers = values[0][1];
      this.female_layers = values[1][1];
    },
    onOpenJSONFile(e) {
      let files = e.target.files || e.dataTransfer.files;
      if (!files.length) return;
      // this.loading = true;
      this.setLoading(true);
      let reader = new FileReader();
      reader.onload = e => {
        this.init(JSON.parse(e.target.result));
        this.setLoading(false);
      };
      reader.readAsText(files[0]);
    },
    copyMaleToFemale() {
      this.female_layers = _.cloneDeep(this.male_layers);
    },
    copyFemaleToMale() {
      this.male_layers = _.cloneDeep(this.female_layers);
    },
    makeJson() {
      if (!this.nameSyntax) {
        this.$refs.nameSyntax.focus();
        alert("please enter name syntax!");
        return;
      }
      var obj = {
        modelo: {
          chico: {},
          chica: {}
        },
        name: this.nameSyntax
      };
      for (var i in this.male_layers) {
        var layer = this.male_layers[i];
        obj.modelo.chico[layer.title] = [];
        for (var j in layer.options) {
          var option = layer.options[j];
          obj.modelo.chico[layer.title].push(option);
        }
      }
      for (var i in this.female_layers) {
        var layer = this.female_layers[i];
        obj.modelo.chica[layer.title] = [];
        for (var j in layer.options) {
          var option = layer.options[j];
          obj.modelo.chica[layer.title].push(option);
        }
      }
      this.updateJson({
        id: this.characterId,
        json: JSON.stringify(obj)
      })
        .then(() => {
          this.$router.go(-1);
          this.$eventHub.$emit("update-success");
        })
        .catch(error => {
          alert("Error!");
          console.log(error);
        });
    }
  }
};
</script>

<style>
svg {
  width: 300px;
}
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
