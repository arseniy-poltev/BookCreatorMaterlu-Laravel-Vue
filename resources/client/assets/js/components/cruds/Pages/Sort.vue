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
              <h3 class="box-title">Sort</h3>
            </div>
            <div class="box-body">
              <!-- <back-buttton></back-buttton> -->
              <router-link
                v-if="$can('page_access')"
                :to="{ name: 'pages.index', id: $route.params.id}"
                class="btn btn-default btn-sm"
              >
                <span class="glyphicon glyphicon-chevron-left"></span> Back to all Pages
              </router-link>
              <button type="button" class="btn btn-default btn-sm" @click="fetchData(bookId)">
                <i class="fa fa-refresh" :class="{'fa-spin': loading}"></i> Refresh
              </button>
              <button v-if="!loading" type="button" class="btn btn-success btn-sm" @click="save">
                <i class="fa fa-save"></i> Save
              </button>
            </div>
            <div class="row" v-if="loading">
              <div class="col-xs-4 col-xs-offset-4">
                <div class="alert text-center">
                  <i class="fa fa-spin fa-refresh"></i> Loading
                </div>
              </div>
            </div>
            <div class="box-body" v-if="!loading">
              <draggable
                class="list-group"
                tag="ul"
                v-model="list"
                v-bind="dragOptions"
                @start="isDragging = true"
                @end="isDragging = false"
              >
                <transition-group type="transition" name="flip-list">
                  <vue-load-image v-for="item in list" :key="item.order" class="image-wrapper">
                    <div
                      slot="image"
                      :data-src="item.thumb_url"
                      @click="item.fixed = !item.fixed"
                      class="image"
                      :style="{ backgroundImage: 'url(' + item.thumb_url + ')',backgroundSize:'contain'}"
                    >
                      <page-status :file_name="item.file_name" :status="item.status"></page-status>
                    </div>
                    <div slot="error">Error</div>
                    <img
                      slot="preloader"
                      class="preloader"
                      src="/gif/loading.gif"
                      style="width:50px"
                    />
                  </vue-load-image>
                </transition-group>
              </draggable>
            </div>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>

<script>
import draggable from "vuedraggable";
import { mapGetters, mapActions } from "vuex";
import VueLoadImage from "vue-load-image";

export default {
  name: "transition-example",
  display: "Transition",
  order: 6,
  components: {
    draggable,
    "vue-load-image": VueLoadImage
  },
  data() {
    return {
      list: [],
      bookId: this.$route.params.id
    };
  },
  methods: {
    ...mapActions("PagesIndex", ["fetchData", "resetState", "changeOrder"]),
    sort() {
      // this.list = this.list.sort((a, b) => a.order - b.order);
    },
    init() {
      this.fetchData(this.$route.params.id).then(() => {
        for (var i in this.data) {
          var item = this.data[i];
          item["order"] = Number(i) + 1;
          this.list.push(item);
        }
      });
    },
    save() {
      var order = [];
      for (var i in this.list) {
        order.push(this.list[i].id);
      }
      var data = {
        bookId: this.bookId,
        order: JSON.stringify(order)
      };
      this.changeOrder(data)
        .then(() => {
          this.$router.push({
            name: "pages.index",
            params: { id: this.bookId }
          });
          this.$eventHub.$emit("update-success");
        })
        .catch(error => {
          console.error(error);
        });
    }
  },
  watch: {
    "$route.params": function() {
      this.init();
    }
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
    ]),

    dragOptions() {
      return {
        animation: 0,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      };
    }
  }
};
</script>

<style scoped>
.image {
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center center;
  width: 150px;
  height: 150px;
}
.image-wrapper {
  float: left;
  border: 2px solid #ebebeb;
  margin: 1px;
  border-radius: 5px;
  cursor: move;
  display: flex;
  width: 150px;
  height: 150px;
}
.preloader {
  margin: auto;

  display: block;
}
.button {
  margin-top: 35px;
}
.flip-list-move {
  transition: transform 0.5s;
}
.no-move {
  transition: transform 0s;
}
.ghost {
  opacity: 0.5;
  background: #c8ebfb;
}
.list-group {
  min-height: 20px;
}
.list-group-item {
  cursor: move;
}
.list-group-item i {
  cursor: pointer;
}
</style>