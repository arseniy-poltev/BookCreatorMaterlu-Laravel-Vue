<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Test Books</h1>
    </section>

    <section class="content row">
      <div class="row" v-if="loading">
        <div class="col-xs-4 col-xs-offset-4">
          <div class="alert text-center">
            <i class="fa fa-spin fa-refresh"></i> Loading
          </div>
        </div>
      </div>
      <div v-if="!loading && proc.length == total">
        <div class="col-xs-6 col-md-4 col-sm-6" v-for="(book,index) in all" v-bind:key="index">
          <div class="card-book">
            <div>
              <img :src="book.img_url" class="book-logo" />
              <span class="book-title">{{book.name}}</span>
            </div>
            <div style="margin-top:20px">
              <button
                type="button"
                class="btn btn-primary btn-lg"
                @click="deployBook(index, book.id)"
                :disabled="proc[index]['processing']"
              >
                <i class="fa fa-spin fa-refresh" v-if="proc[index]['processing']"></i>
                <i class="fa fa-upload" v-if="!proc[index]['processing']"></i>
                Deploy
              </button>
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
      proc: []
    };
  },
  mounted() {
    this.fetchData().then(() => {
      for (var i in this.all) {
        this.proc.push({
          processing: false
        });
      }
    });
  },
  destroyed() {
    this.resetState();
  },
  computed: {
    ...mapGetters("BooksIndex", ["all", "total", "loading"])
  },
  methods: {
    ...mapActions("BooksIndex", ["fetchData", "sendBook", "resetState"]),
    deployBook(index, id) {
      this.proc[index]["processing"] = true;
      this.sendBook(id)
        .then(() => {})
        .catch(error => {})
        .finally(() => {
          this.proc[index]["processing"] = false;
        });
    }
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
  font-size: 30px;
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


