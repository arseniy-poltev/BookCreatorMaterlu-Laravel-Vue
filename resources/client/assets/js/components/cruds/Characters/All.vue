<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Characters</h1>
    </section>

    <section class="content">
      <div class="row" v-if="loading">
        <div class="col-xs-4 col-xs-offset-4">
          <div class="alert text-center">
            <i class="fa fa-spin fa-refresh"></i> Loading
          </div>
        </div>
      </div>
      <div class="row col-md-12" v-if="!loading">
        <div class="col-xs-6 col-md-4 col-sm-6" v-for="(book,index) in all" v-bind:key="index">
          <div class="card-book">
            <div class>
              <img :src="book.img_url" class="book-logo" />
              <span class="book-title">{{book.name}}</span>
              <router-link
                v-if="$can('character_create')"
                :to="{ name: 'characters.create' , params: { id: book.id }}"
                class="btn btn-danger btn-sm"
                style="float:right"
              >
                <i class="fa fa-upload"></i> Add new
              </router-link>
            </div>
            <div style="margin-top:5px" class="row">
              <div class="col-md-6" v-for="(character,i) in book.characters" v-bind:key="i">
                <character-view :data="character"></character-view>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>
<script>
import CharacterView from "../../CharacterView";
import { mapGetters, mapActions } from "vuex";
export default {
  created() {
    this.fetchAll();
  },
  destroyed() {
    this.resetState();
  },
  computed: {
    ...mapGetters("CharactersIndex", ["all", "total", "loading"])
  },
  methods: {
    ...mapActions("CharactersIndex", ["fetchAll", "resetState"])
  },
  components: {
    "character-view": CharacterView
  }
};
</script>
<style scoped>
.card-book {
  background-color: #fff;
  box-shadow: 0 2px 63px 3px rgba(53, 53, 53, 0.1);
  border-radius: 20px;
  /* margin: 0 auto; */
  margin: 5px;
  padding: 12px;
  /* display: block; */
  /* align-items: flex-start; */
}
.book-logo {
  max-width: 150px;
}
.book-title {
  vertical-align: middle;
  font-size: 20px;
  color: #0b342e;
  font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande",
    "Lucida Sans", Arial, sans-serif;
}
.card-character {
  background-color: #fff;
  box-shadow: 0 2px 63px 3px rgba(53, 53, 53, 0.1);
  border-radius: 20px;
  /* margin: 0 auto; */
  padding: 8px;
}
.character-image {
  width: 250px;
}
</style>


