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
      <div v-if="!loading && procPDF.length == total">
        <div class="col-xs-6 col-md-4 col-sm-6" v-for="(book,index) in all" v-bind:key="index">
          <div class="card-book">
            <div>
              <img :src="book.img_url" class="book-logo" />
              <span class="book-title">{{book.name}}</span>
            </div>
            <div style="margin-top:20px">
              <router-link
                :to="{ name: 'books.pdf_customize' , params: { id: book.id }}"
                class="btn btn-primary btn-lg"
              >
                <i class="fa fa-file-pdf-o"></i> Customize PDF
              </router-link>
              <!-- <button type="button" class="btn btn-primary btn-lg" @click="goToBook(index)">
                <i class="fa fa-file-pdf-o"></i> Customize PDF
              </button>-->
              <!-- <button
                type="button"
                class="btn btn-danger btn-lg"
                @click="makePDF(index, book.id, 'web')"
                :disabled="procPDF[index]['web']"
              >
                <i class="fa fa-spin fa-refresh" v-if="procPDF[index]['web']"></i>
                Web PDF
              </button>
              <a :href="'/uploads/book/' + book.id + '/web.pdf'" target="_blank">View WebPDF</a>
              <button
                type="button"
                class="btn btn-success btn-lg"
                @click="makePDF(index, book.id, 'print')"
                :disabled="procPDF[index]['print']"
              >
                <i class="fa fa-spin fa-refresh" v-if="procPDF[index]['print']"></i>
                Print PDF
              </button>
              <a
                :href="'/uploads/book/' + book.id + '/print.pdf'"
                :download="book.name + '_print'"
              >Download Print PDF</a>-->
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
      procPDF: []
    };
  },
  mounted() {
    this.fetchData().then(() => {
      for (var i in this.all) {
        this.procPDF.push({
          web: false,
          print: false
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
    ...mapActions("BooksIndex", ["fetchData", "resetState", "generatePDF"]),
    goToBook(index) {
      // this.$store.dispatch("BooksSingle/setItem", this.all[index]);
      this.$router.push({
        name: "books.pdf_customize",
        params: { id: this.all[index].id }
      });
    },
    makePDF(index, bookId, type) {
      this.procPDF[index][type] = true;
      this.generatePDF({ id: bookId, type: type })
        .then(() => {})
        .catch(error => {
          alert(error);
        })
        .finally(() => {
          this.procPDF[index][type] = false;
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


