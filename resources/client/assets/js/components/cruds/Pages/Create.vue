
<template>
  <section class="content-wrapper" style="min-height: 960px;">
    <section class="content-header">
      <h1>Pages</h1>
    </section>

    <section class="content">
      <div class="box example-full">
        <div class="box-header with-border">
          <h3 class="box-title">Create pages</h3>
        </div>
        <div v-show="$refs.upload && $refs.upload.dropActive" class="drop-active">
          <h3>Drop files to upload</h3>
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
        </div>

        <div class="box-body upload" v-show="!isOption">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th style="width: 150px">Thumb</th>
                  <th>Name</th>
                  <th>Size</th>
                  <th>Speed</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!files.length">
                  <td colspan="7">
                    <div class="text-center p-5">
                      <h4>
                        Drop files anywhere to upload
                        <br />or
                      </h4>
                      <label :for="name" class="btn btn-lg btn-primary">Select Files</label>
                    </div>
                  </td>
                </tr>
                <tr v-for="(file, index) in files" :key="file.id">
                  <td>{{index}}</td>
                  <td>
                    <img v-if="file.thumb" :src="file.thumb" width="100" height="auto" />
                    <span v-else>No Image</span>
                  </td>
                  <td>
                    <div class="filename">{{file.name}}</div>
                    <div class="progress" v-if="file.active || file.progress !== '0.00'">
                      <div
                        :class="{'progress-bar': true, 'progress-bar-striped': true, 'bg-danger': file.error, 'progress-bar-animated': file.active}"
                        role="progressbar"
                        :style="{width: file.progress + '%'}"
                      >{{file.progress}}%</div>
                    </div>
                  </td>
                  <td>{{file.size | prettyBytes(1, true) }}</td>
                  <td>{{file.speed | prettyBytes(1, true) }}</td>

                  <td v-if="file.error">{{file.error}}</td>
                  <td v-else-if="file.success">success</td>
                  <td v-else-if="file.active">active</td>
                  <td v-else></td>
                  <td>
                    <div class="input-group-btn">
                      <button
                        type="button"
                        class="btn btn-success dropdown-toggle"
                        data-toggle="dropdown"
                        aria-expanded="false"
                      >
                        Action
                        <span class="fa fa-caret-down"></span>
                      </button>
                      <ul class="dropdown-menu">
                        <li>
                          <a
                            :class="{'dropdown-item': true, disabled: !file.active}"
                            href="#"
                            @click.prevent="file.active ? $refs.upload.update(file, {error: 'cancel'}) : false"
                          >Cancel</a>
                        </li>
                        <li v-if="file.active">
                          <a
                            class="dropdown-item"
                            href="#"
                            @click.prevent="$refs.upload.update(file, {active: false})"
                          >Abort</a>
                        </li>
                        <li
                          v-else-if="file.error && file.error !== 'compressing' && $refs.upload.features.html5"
                        >
                          <a
                            class="dropdown-item"
                            href="#"
                            @click.prevent="$refs.upload.update(file, {active: true, error: '', progress: '0.00'})"
                          >Retry upload</a>
                        </li>
                        <li v-else>
                          <a
                            :class="{'dropdown-item': true, disabled: file.success || file.error === 'compressing'}"
                            href="#"
                            @click.prevent="file.success || file.error === 'compressing' ? false : $refs.upload.update(file, {active: true})"
                          >Upload</a>
                        </li>
                        <li>
                          <a
                            class="dropdown-item"
                            href="#"
                            @click.prevent="$refs.upload.remove(file)"
                          >Remove</a>
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="example-foorer">
            <div class="footer-status float-right">
              Drop: {{$refs.upload ? $refs.upload.drop : false}},
              Active: {{$refs.upload ? $refs.upload.active : false}},
              Uploaded: {{$refs.upload ? $refs.upload.uploaded : true}},
              Drop active: {{$refs.upload ? $refs.upload.dropActive : false}}
            </div>
            <div class="btn-group">
              <file-upload
                class="btn btn-primary dropdown-toggle"
                :post-action="postAction"
                :extensions="extensions"
                :accept="accept"
                :multiple="multiple"
                :directory="directory"
                :size="size || 0"
                :thread="thread < 1 ? 1 : (thread > 5 ? 5 : thread)"
                :headers="headers"
                :data="data"
                :drop="drop"
                :drop-directory="dropDirectory"
                :add-index="addIndex"
                v-model="files"
                @input-filter="inputFilter"
                @input-file="inputFile"
                ref="upload"
              >
                <i class="fa fa-plus"></i>
                Select
              </file-upload>
              <ul class="dropdown-menu">
                <!-- <li>
                  <label class="dropdown-item" :for="name">Add files</label>
                </li>-->
                <li>
                  <a class="dropdown-item" href="#" @click="onAddFolader">Add folder</a>
                </li>
                <!-- <li>
                  <a class="dropdown-item" href="#" @click.prevent="addData.show = true">Add data</a>
                </li>-->
              </ul>
            </div>
            <button
              type="button"
              class="btn btn-success"
              v-if="!$refs.upload || !$refs.upload.active"
              @click.prevent="$refs.upload.active = true"
            >
              <i class="fa fa-arrow-up" aria-hidden="true"></i>
              Start Upload
            </button>
            <button
              type="button"
              class="btn btn-danger"
              v-else
              @click.prevent="$refs.upload.active = false"
            >
              <i class="fa fa-stop" aria-hidden="true"></i>
              Stop Upload
            </button>
          </div>
        </div>
      </div>
    </section>
  </section>
</template>


<script>
import FileUpload from "vue-upload-component";

export default {
  components: {
    FileUpload
  },

  data() {
    return {
      files: [],
      accept: "image/svg+xml",
      extensions: "svg",
      // extensions: ['gif', 'jpg', 'jpeg','png', 'webp'],
      // extensions: /\.(gif|jpe?g|png|webp)$/i,
      minSize: 100,
      size: 1024 * 1024 * 200,
      multiple: true,
      directory: false,
      drop: true,
      dropDirectory: true,
      addIndex: false,
      thread: 3,
      name: "file",
      postAction: "/api/v1/upload-page",
      putAction: "/upload/put",
      headers: {
        "X-CSRF-TOKEN": document.head.querySelector('meta[name="csrf-token"]')
          .content
      },
      data: {
        book_id: this.$route.params.id
      },
      autoCompress: 1024 * 1024,
      uploadAuto: false,
      isOption: false,
      addData: {
        show: false,
        name: "",
        type: "",
        content: ""
      },
      editFile: {
        show: false,
        name: ""
      }
    };
  },

  watch: {
    "editFile.show"(newValue, oldValue) {
      // 关闭了 自动删除 error
      if (!newValue && oldValue) {
        this.$refs.upload.update(this.editFile.id, {
          error: this.editFile.error || ""
        });
      }
      if (newValue) {
        this.$nextTick(function() {
          if (!this.$refs.editImage) {
            return;
          }
          let cropper = new Cropper(this.$refs.editImage, {
            autoCrop: false
          });
          this.editFile = {
            ...this.editFile,
            cropper
          };
        });
      }
    },
    "addData.show"(show) {
      if (show) {
        this.addData.name = "";
        this.addData.type = "";
        this.addData.content = "";
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    if (this.$refs.upload.active) {
      this.$refs.upload.active = false;
    }
    setTimeout(function() {
      next();
    }, 100);
  },
  methods: {
    inputFilter(newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        // Before adding a file
        // 添加文件前
        // Filter system files or hide files
        // 过滤系统文件 和隐藏文件
        if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
          return prevent();
        }
        // Filter php html js file
        // 过滤 php html js 文件
        if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
          return prevent();
        }
        // Automatic compression
        // 自动压缩
        // if (
        //   newFile.file &&
        //   newFile.type.substr(0, 6) === "image/" &&
        //   this.autoCompress > 0 &&
        //   this.autoCompress < newFile.size
        // ) {
        //   newFile.error = "compressing";
        //   const imageCompressor = new ImageCompressor(null, {
        //     convertSize: Infinity,
        //     maxWidth: 512,
        //     maxHeight: 512
        //   });
        //   imageCompressor
        //     .compress(newFile.file)
        //     .then(file => {
        //       this.$refs.upload.update(newFile, {
        //         error: "",
        //         file,
        //         size: file.size,
        //         type: file.type
        //       });
        //     })
        //     .catch(err => {
        //       this.$refs.upload.update(newFile, {
        //         error: err.message || "compress"
        //       });
        //     });
        // }
      }
      // if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
      //   // Create a blob field
      //   // 创建 blob 字段
      //   newFile.blob = "";
      //   let URL = window.URL || window.webkitURL;
      //   if (URL && URL.createObjectURL) {
      //     newFile.blob = URL.createObjectURL(newFile.file);
      //   }
      //   // Thumbnails
      //   // 缩略图
      //   newFile.thumb = "";
      //   if (newFile.blob && newFile.type.substr(0, 6) === "image/") {
      //     newFile.thumb = newFile.blob;
      //   }
      // }
    },
    // add, update, remove File Event
    inputFile(newFile, oldFile) {
      if (newFile && oldFile) {
        // update
        if (newFile.active && !oldFile.active) {
          // beforeSend
          // min size
          if (
            newFile.size >= 0 &&
            this.minSize > 0 &&
            newFile.size < this.minSize
          ) {
            this.$refs.upload.update(newFile, { error: "size" });
          }
        }
        if (newFile.progress !== oldFile.progress) {
          // progress
        }
        if (newFile.error && !oldFile.error) {
          // error
        }
        if (newFile.success && !oldFile.success) {
          // success
        }
      }
      if (!newFile && oldFile) {
        // remove
        if (oldFile.success && oldFile.response.id) {
          // $.ajax({
          //   type: 'DELETE',
          //   url: '/upload/delete?id=' + oldFile.response.id,
          // })
        }
      }
      // Automatically activate upload
      if (
        Boolean(newFile) !== Boolean(oldFile) ||
        oldFile.error !== newFile.error
      ) {
        if (this.uploadAuto && !this.$refs.upload.active) {
          this.$refs.upload.active = true;
        }
      }
    },
    alert(message) {
      alert(message);
    },
    onEditFileShow(file) {
      this.editFile = { ...file, show: true };
      this.$refs.upload.update(file, { error: "edit" });
    },
    onEditorFile() {
      if (!this.$refs.upload.features.html5) {
        this.alert("Your browser does not support");
        this.editFile.show = false;
        return;
      }
      let data = {
        name: this.editFile.name
      };
      if (this.editFile.cropper) {
        let binStr = atob(
          this.editFile.cropper
            .getCroppedCanvas()
            .toDataURL(this.editFile.type)
            .split(",")[1]
        );
        let arr = new Uint8Array(binStr.length);
        for (let i = 0; i < binStr.length; i++) {
          arr[i] = binStr.charCodeAt(i);
        }
        data.file = new File([arr], data.name, { type: this.editFile.type });
        data.size = data.file.size;
      }
      this.$refs.upload.update(this.editFile.id, data);
      this.editFile.error = "";
      this.editFile.show = false;
    },
    // add folader
    onAddFolader() {
      if (!this.$refs.upload.features.directory) {
        this.alert("Your browser does not support");
        return;
      }
      let input = this.$refs.upload.$el.querySelector("input");
      input.directory = true;
      input.webkitdirectory = true;
      this.directory = true;
      input.onclick = null;
      input.click();
      input.onclick = e => {
        this.directory = false;
        input.directory = false;
        input.webkitdirectory = false;
      };
    },
    onAddData() {
      this.addData.show = false;
      if (!this.$refs.upload.features.html5) {
        this.alert("Your browser does not support");
        return;
      }
      let file = new window.File([this.addData.content], this.addData.name, {
        type: this.addData.type
      });
      this.$refs.upload.add(file);
    }
  }
};
</script>
<style>
.example-full .btn-group .dropdown-menu {
  display: block;
  visibility: hidden;
  transition: all 0.2s;
}
.example-full .btn-group:hover > .dropdown-menu {
  visibility: visible;
}
.example-full label.dropdown-item {
  margin-bottom: 0;
}
.example-full .btn-group .dropdown-toggle {
  margin-right: 0.6rem;
}
.example-full .filename {
  margin-bottom: 0.3rem;
}
.example-full .btn-is-option {
  margin-top: 0.25rem;
}
.example-full .example-foorer {
  padding: 0.5rem 0;
  border-top: 1px solid #e9ecef;
  border-bottom: 1px solid #e9ecef;
}
.example-full .edit-image img {
  max-width: 100%;
}
.example-full .edit-image-tool {
  margin-top: 0.6rem;
}
.example-full .edit-image-tool .btn-group {
  margin-right: 0.6rem;
}
.example-full .footer-status {
  padding-top: 0.4rem;
}
.example-full .drop-active {
  top: 0;
  bottom: 0;
  right: 0;
  left: 0;
  position: fixed;
  z-index: 9999;
  opacity: 0.6;
  text-align: center;
  background: #000;
}
.example-full .drop-active h3 {
  margin: -0.5em 0 0;
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  -webkit-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
  font-size: 40px;
  color: #fff;
  padding: 0;
}
</style>